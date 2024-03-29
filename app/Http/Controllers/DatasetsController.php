<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\ExecutiveAuthority;
use App\Models\Dataset;
use App\Models\Resource;
use App\Models\Statistic;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

use App\Helpers\AuthApi;

class DatasetsController extends Controller
{
    public $datasourceUrl = 'https://data.lutskrada.gov.ua';
    const DAYS_TO_REMIND_ABOUT_DATASET_UPDATE = 7;
    //field names used for making changes in db
    public $readFieldsInDB = ["executive_authorities.display_name", "datasets.title", "resources.name"];
    //field names used for validating incoming data
    public $readFieldsFromFrontend = ["executive_authority_name", "dataset_title", "resource_name"];

    /**
     * receive data from data.lutskrada.gov.ua and place it in DB
     */
    public function receive(Request $request) {
        //getting auth information
        $auth = AuthAPI::isAuthenticated($request->bearerToken(), $request->ip());
        $userId = $auth->user->id;

        //removing old datasets information, for current authenticated user
        DB::table('executive_authorities')->where('user_id', '=', $userId)->delete();
        DB::table('datasets')->where('user_id', '=', $userId)->delete();
        DB::table('resources')->where('user_id', '=', $userId)->delete();
        DB::table('statistics')->where('user_id', '=', $userId)->delete();

        // creating request context
        $opts = [
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ],
        ];
        $context = stream_context_create($opts);

        //setting executive_authorities
        $organizationsFromAPI = json_decode(file_get_contents("{$this->datasourceUrl}/api/3/action/organization_list?all_fields=true", false, $context), true);
        foreach ($organizationsFromAPI['result'] as $key => $organization) {
            ExecutiveAuthority::create([
                'id' => $organization['id'],
                'user_id' => $userId,
                'name' => $organization['name'],
                'display_name' => $organization['display_name'],
            ]);
        }

        //setting datasets
        $datasetsFromAPI = json_decode(file_get_contents("{$this->datasourceUrl}/api/3/action/package_search?q=*:*&rows=99999", false, $context), true);
        foreach ($datasetsFromAPI['result']['results'] as $key => $dataset) {
            $nextDatasetUpdateDate =
                $this->getDatasetNextUpdateDate($dataset["update_frequency"], $dataset["metadata_modified"]);
            $datasetType = $this->getDatasetType($dataset["update_frequency"], $nextDatasetUpdateDate);
            $datasetTagsString = $this->getDatasetTagsString($dataset["tags"]);
            $datasetFormatsString = $this->getDatasetFormatsString($dataset["resources"]);

            Dataset::create([
                'id' => $dataset["id"],
                'user_id' => $userId,
                'executive_authority_name' => $dataset["organization"]["name"],
                'state' => $dataset["state"],
                'name' => $dataset["name"],
                'title' => $dataset["title"],
                'description' => $dataset["notes"],
                'tags' => $datasetTagsString,
                'purpose' => $dataset["purpose_of_collecting_information"],
                'formats' => $datasetFormatsString,
                'last_updated_at' => $dataset["metadata_modified"],
                'update_frequency' => $dataset["update_frequency"],
                'next_update_at' => $nextDatasetUpdateDate,
                'days_to_update' => Carbon::now()->diffInDays($nextDatasetUpdateDate, false),
                'maintainer_name' => $dataset["maintainer"],
                'maintainer_email' => $dataset["maintainer_email"],
                'type' => $datasetType,
            ]);

            //setting resources
            foreach ($dataset["resources"] as $key => $resource) {
                Resource::create([
                    'id' => $resource["id"],
                    'user_id' => $userId,
                    'dataset_id' => $resource["package_id"],
                    'state' => $resource["state"],
                    'name' => $resource["name"],
                    'description' => $resource["description"],
                    'format' => $resource["format"],
                    'url' => $resource["url"],
                    'validation_status' => !isset($resource["validation_status"]) ? null : $resource["validation_status"],
                ]);
            }
        }

        $this->setStatistic($userId);
    }

    /**
     * receive data from DB
     */
    public function read(Request $request) {
        $compiledRegexRule = "";
        $validationRules = [
            'mode' => ["nullable", "string", "regex:/^all$|^debtor$|^reminder$|^inactive$/i"],
        ];

        foreach ($this->readFieldsFromFrontend as $key => $field) {
            /**
             * forming regular exp. for validation of ordering depending on fields name
             * (makes possible to set order only with existing fields)
             */
            $compiledRegexRule .= "^{$field}$";
            $key != count($this->readFieldsFromFrontend) - 1 ? $compiledRegexRule .= "|" : "";

            /**
             * setting validation rule for each field
             * (filter value of its field and filter mode)
             */
            $validationRules["{$field}FilterValue"] = "string|nullable";
            $validationRules["{$field}FilterMode"] = ["string", "regex:/^include$|^exclude$|^more$|^less$|^equal$|^notequal$/i", "nullable"];
        }

        $data = $request->validate($validationRules);
        $auth = AuthAPI::isAuthenticated($request->bearerToken(), $request->ip());
        $userId = $auth->user->id;

        $datasets = DB::table('executive_authorities')
            ->select(
                'executive_authorities.id as executive_authority_id',
                'executive_authorities.user_id as executive_authority_user_id',
                'executive_authorities.name as executive_authority_name',
                'executive_authorities.display_name as executive_authority_display_name',
                'datasets.id as dataset_id',
                'datasets.user_id as dataset_user_id',
                'datasets.state as dataset_state',
                'datasets.name as dataset_name',
                'datasets.title as dataset_title',
                'datasets.description as dataset_description',
                'datasets.purpose as dataset_purpose',
                'datasets.last_updated_at as dataset_last_updated_at',
                'datasets.update_frequency as dataset_update_frequency',
                'datasets.next_update_at as dataset_next_update_at',
                'datasets.days_to_update as dataset_days_to_update',
                'datasets.maintainer_name as dataset_maintainer_name',
                'datasets.maintainer_email as dataset_maintainer_email',
                'datasets.type as dataset_type',
                'resources.id as resource_id',
                'resources.user_id as resource_user_id',
                'resources.state as resource_state',
                'resources.name as resource_name',
                'resources.description as resource_description',
                'resources.format as resource_format',
                'resources.url as resource_url',
                'resources.validation_status as resource_validation_status',
            )
            ->join('datasets', 'executive_authorities.name', '=', 'datasets.executive_authority_name')
            ->join('resources', 'resources.dataset_id', '=', 'datasets.id')
            ->orderBy('executive_authorities.display_name','asc')
            ->orderBy('datasets.title','asc')
            ->orderBy('resources.name','asc')
            ->where([
                ['executive_authorities.user_id', '=', $userId],
                ['datasets.user_id', '=', $userId],
                ['resources.user_id', '=', $userId],
            ]);

        //forming 'WHERE' query for each field
        foreach ($this->readFieldsInDB as $key => $field) {
            $searchValue = $data["{$this->readFieldsFromFrontend[$key]}FilterValue"];
            //'WHERE' operator like: '>', '<>', etc
            $searchOperator = $this->getWhereOperator($data["{$this->readFieldsFromFrontend[$key]}FilterMode"]);

            //special conditions in case of 'like' or 'notLike' operator
            if ($searchValue != null && $searchOperator != null) {
                if ($searchOperator === "like") {
                    $datasets->where($field, "like", "%{$searchValue}%");
                } elseif ($searchOperator === "notLike") {
                    $datasets->whereNot(function ($query) use ($searchValue, $field) {
                        $query->where($field, "like", "%{$searchValue}%");
                    });
                } else {
                    $datasets->where($field, $searchOperator, $searchValue);
                }
            }
        }

        //setting search mode (normal/debtor/reminder/inactive)
        switch ($data["mode"]) {
            case "all":
                break;
            case "normal":
                $datasets->where('type', '=', 'normal');
                break;
            case "debtor":
                $datasets->where('type', '=', 'debtor');
                break;
            case "reminder":
                $datasets->where('type', '=', 'reminder');
                break;
            case "inactive":
                $datasets->where('type', '=', 'inactive');
                break;
            default:
                break;
        }

        //initial values after receiving datasets
        $statistic["initial"] = [];
        //actual values after filtering datasets, etc
        $statistic["actual"] = [];

        /**
         * TODO:intendent datasets instead of user_id binding
         */
        $outputArray = $this->sortDatasetsAndWriteStatistic($datasets->get()->toArray(), $statistic["actual"]);

        //write statistic
        $statistic["initial"] = Statistic::where('user_id', $userId)->get()->toArray();
        $statistic["initial"] = $statistic["initial"][0];
        $statistic["initial"]["created_at"] = new Carbon($statistic["initial"]["created_at"]);
        $statistic["initial"]["created_at"]->tz = 'Europe/Kyiv';
        $statistic["initial"]["created_at"] = $statistic["initial"]["created_at"]->format('d.m.Y H:i');
        $outputArray["statistic"] = $statistic;

        return response()->json($outputArray);
    }

    /**
     * Calculating dataset type depends of dataset update frequency and next update date
     */
    private function getDatasetType($updateFrequency, $nextDatasetUpdateDate) {
        $difference = Carbon::now()->diffInDays($nextDatasetUpdateDate, false);

        if ($updateFrequency == 'immediately after making changes'
            || $updateFrequency == 'more than once a day') {
            return 'normal';
        }
        if ($updateFrequency == 'no longer updated') {
            return 'inactive';
        }
        if ($difference >= 0 && $difference <= self::DAYS_TO_REMIND_ABOUT_DATASET_UPDATE) {
            return 'reminder';
        }
        if ($difference > self::DAYS_TO_REMIND_ABOUT_DATASET_UPDATE) {
            return 'normal';
        }
        if ($difference < 0) {
            return 'debtor';
        }

        return 'normal';
    }

    /**
     * Combines several tags into one string
     */
    private function getDatasetTagsString($tagObjectFromAPI) {
        $combinedTagString = '';

        foreach($tagObjectFromAPI as $key => $tag) {
            $combinedTagString = $combinedTagString.$tag["display_name"];

            if (next($tagObjectFromAPI)) {
                $combinedTagString = $combinedTagString.', ';
            }
        }

        return $combinedTagString;
    }

    /**
     * Combines several resource formats into one string
     */
    private function getDatasetFormatsString($resources) {
        $combinedResourceString = '';

        foreach($resources as $key => $resource) {

            if ($key == array_key_first($resources)) {
                $combinedResourceString = $resource["format"];
            } else {

                if (Str::contains($combinedResourceString, $resource["format"]) == false) {
                    //if first item was actualy format, not a " ", or empty string at all
                    if (strlen($combinedResourceString) > 1) {
                        $combinedResourceString = "{$combinedResourceString}, {$resource["format"]}";
                    }
                }
            }
        }

        return $combinedResourceString;
    }

    /**
     * Calculates dataset next update date depends of dataset update frequency and
     * dataset last update date
     */
    private function getDatasetNextUpdateDate($updateFrequency, $lastUpdatedAt) {
        $nextDatasetUpdateDate = new Carbon($lastUpdatedAt);

        switch ($updateFrequency) {
            case 'immediately after making changes':
                break;
            case 'more than once a day':
                // $nextDatasetUpdateDate->addDay();
                break;
            case 'once a day':
                $nextDatasetUpdateDate->addDay();
                break;
            case 'once a week':
                $nextDatasetUpdateDate->addWeek();
                break;
            case 'once a month':
                $nextDatasetUpdateDate->addMonth();
                break;
            case 'once a quarter':
                $nextDatasetUpdateDate->addQuarter();
                break;
            case 'once a half year':
                $nextDatasetUpdateDate->addMonths(6);
                break;
            case 'once a year':
                $nextDatasetUpdateDate->addYear();
                break;
            case 'no longer updated':
                break;
            default:
                break;
        }

        return $nextDatasetUpdateDate;
    }

    /**
     * Receives unsorted (but ordered by ea name, dataset name and resource name)
     * array from database and makes it json-like view
     * (for general list)
     *
     * also writes statistic into external array through "&"
     */
    private function sortDatasetsAndWriteStatistic($arrayOfDatasetsFromDB, &$actualStatistic) {
        $arrayOfDatasetsFromDB = json_decode(json_encode($arrayOfDatasetsFromDB), true);
        //for statistic
        $amountOfExecutiveAuthorities = 0;
        $amountOfDatasets = 0;
        $amountOfResources = 0;
        $amountOfDebtors = 0;
        $amountOfReminders = 0;
        $amountOfInactives = 0;
        $outputArray["executive_authorities"] = [];

        $previousExecutiveAuthorityID = null;
        $currentExecutiveAuthorityID = null;
        $currentExecutiveAuthorityArrayIndex = -1;

        $previousDatasetID = null;
        $currentDatasetID = null;
        $currentDatasetArrayIndex = -1;

        $previousResourceID = null;
        $currentResourceID = null;
        $currentResourceArrayIndex = -1;

        foreach ($arrayOfDatasetsFromDB as $key => $row) {
            $currentExecutiveAuthorityID = $row["executive_authority_id"];
            $currentDatasetID = $row["dataset_id"];
            $currentResourceID = $row["resource_id"];

            //filling array with executive authorities
            if ($currentExecutiveAuthorityID != $previousExecutiveAuthorityID) {
                $currentExecutiveAuthorityArrayIndex += 1;
                $currentDatasetArrayIndex = -1;
                $amountOfExecutiveAuthorities += 1;

                $outputArray["executive_authorities"][] = [
                    'id' => $row["executive_authority_id"],
                    'name' => $row["executive_authority_name"],
                    'display_name' => $row["executive_authority_display_name"],
                ];
            }

            //filling array with datasets inside executive authorities
            if ($currentDatasetID != $previousDatasetID) {
                $currentDatasetArrayIndex += 1;
                $currentResourceArrayIndex = -1;
                $amountOfDatasets += 1;

                switch ($row["dataset_type"]) {
                    case 'debtor':
                        $amountOfDebtors += 1;
                        break;
                    case 'reminder':
                        $amountOfReminders += 1;
                        break;
                    case 'inactive':
                        $amountOfInactives += 1;
                        break;
                    default:
                        break;
                }

                $outputArray
                    ["executive_authorities"][$currentExecutiveAuthorityArrayIndex]
                    ["datasets"][] = [
                    'id' => $row["dataset_id"],
                    'state' => $row["dataset_state"],
                    'title' => $row["dataset_title"],
                    'description' => $row["dataset_description"],
                    'purpose' => $row["dataset_purpose"],
                    'last_updated_at' => $this->convertTimeToUkrLocale($row["dataset_last_updated_at"]),
                    'update_frequency' => $row["dataset_update_frequency"],
                    'next_update_at' => $this->convertTimeToUkrLocale($row["dataset_next_update_at"]),
                    'days_to_update' => $row["dataset_days_to_update"],
                    'maintainer_name' => $row["dataset_maintainer_name"],
                    'maintainer_email' => $row["dataset_maintainer_email"],
                    'type' => $row["dataset_type"],
                ];
            }

            //filling array with resources inside datasets
            if ($currentResourceID != $previousResourceID) {
                $currentResourceArrayIndex += 1;
                $amountOfResources += 1;

                $outputArray
                    ["executive_authorities"][$currentExecutiveAuthorityArrayIndex]
                    ["datasets"][$currentDatasetArrayIndex]
                    ["resources"][] = [
                    'id' => $row["resource_id"],
                    'state' => $row["resource_state"],
                    'name' => $row["resource_name"],
                    'description' => $row["resource_description"],
                    'format' => $row["resource_format"],
                    'url' => $row["resource_url"],
                    'validation_status' => $row["resource_validation_status"],
                ];
            }

            $previousExecutiveAuthorityID = $row["executive_authority_id"];
            $previousDatasetID = $row["dataset_id"];
            $previousResourceID = $row["resource_id"];
        }

        $actualStatistic["executive_authorities"] = $amountOfExecutiveAuthorities;
        $actualStatistic["datasets"] = $amountOfDatasets;
        $actualStatistic["resources"] = $amountOfResources;
        $actualStatistic["debtors"] = $amountOfDebtors;
        $actualStatistic["reminders"] = $amountOfReminders;
        $actualStatistic["inactives"] = $amountOfInactives;

        return $outputArray;
    }

    /**
     * returns array with statistic
     */
    private function setStatistic($userId) {
        Statistic::create([
            "user_id" => $userId,
            "executive_authorities" =>
                DB::table('executive_authorities')
                ->where('user_id', '=', $userId)->count(),
            "datasets" =>
                DB::table('datasets')
                ->where('user_id', '=', $userId)->count(),
            "resources" =>
                DB::table('resources')
                ->where('user_id', '=', $userId)->count(),
            "debtors" =>
                DB::table('datasets')
                ->where('user_id', '=', $userId)
                ->where('type', '=', 'debtor')->count(),
            "reminders" =>
                DB::table('datasets')
                ->where('user_id', '=', $userId)
                ->where('type', '=', 'reminder')->count(),
            "inactives" =>
                DB::table('datasets')
                ->where('user_id', '=', $userId)
                ->where('type', '=', 'inactive')->count(),
        ]);
    }

    private function getWhereOperator($operatorName) {
        $equality = [
            "include" => "like",
            "exclude" => "notLike",
            "more" => ">",
            "less" => "<",
            "equal"=> "=",
            "notequal" => "<>",
        ];

        return isset($equality[$operatorName]) ? $equality[$operatorName] : null;
    }

    private function convertTimeToUkrLocale($timeFromDb) {
        $time = new Carbon($timeFromDb);
        $time->tz = 'Europe/Kyiv';
        $time = $time->format('d.m.Y H:i');
        return $time;
    }
}

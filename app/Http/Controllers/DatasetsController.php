<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\ExecutiveAuthority;
use App\Models\Dataset;
use App\Models\Resource;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Helpers\AuthApi;

class DatasetsController extends Controller
{
    const DAYS_TO_REMIND_ABOUT_DATASET_UPDATE = 7;

    public function receive(Request $request) {
        //getting auth information
        $auth = AuthAPI::isAuthenticated($request->bearerToken(), $request->ip());
        //removing old datasets information, for current authenticated user
        DB::table('executive_authorities')->where('user_id', '=', $auth->user->id)->delete();
        DB::table('datasets')->where('user_id', '=', $auth->user->id)->delete();
        DB::table('resources')->where('user_id', '=', $auth->user->id)->delete();

        //setting executive_authorities
        $organizationsFromAPI = json_decode(file_get_contents('https://data.lutskrada.gov.ua/api/3/action/organization_list?all_fields=true'), true);
        foreach ($organizationsFromAPI['result'] as $key => $organization) {
            ExecutiveAuthority::create([
                'id' => $organization['id'],
                'user_id' => $auth->user->id,
                'name' => $organization['name'],
                'display_name' => $organization['display_name'],
            ]);
        }
        $organisationsFromDB = DB::table('executive_authorities')->where('user_id', '=', $auth->user->id)->get()->toArray();

        //setting datasets
        $datasetsFromAPI = json_decode(file_get_contents('https://data.lutskrada.gov.ua/api/3/action/package_search?q=*:*&rows=99999'), true);
        foreach ($datasetsFromAPI['result']['results'] as $key => $dataset) {
            $nextDatasetUpdateDate =
                $this->getDatasetNextUpdateDate($dataset["update_frequency"], $dataset["metadata_modified"]);
            $datasetType = $this->getDatasetType($dataset["update_frequency"], $nextDatasetUpdateDate);

            Dataset::create([
                'id' => $dataset["id"],
                'user_id' => $auth->user->id,
                'executive_authority_name' => $dataset["organization"]["name"],
                'state' => $dataset["state"],
                'name' => $dataset["name"],
                'title' => $dataset["title"],
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
                    'user_id' => $auth->user->id,
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
        $datasetsFromDB = DB::table('datasets')->where('user_id', '=', $auth->user->id)->get()->toArray();

        return response()->json($datasetsFromDB);
    }

    /**
     * Calculating dataset type depends of dataset update frequency and next update date
     */
    private function getDatasetType($updateFrequency, $nextDatasetUpdateDate) {
        $difference = Carbon::now()->diffInDays($nextDatasetUpdateDate, false);

        if ($updateFrequency == 'immediately after making changes') {
            return 'normal';
        }
        if ($updateFrequency == 'no longer updated') {
            return 'inactive';
        }
        if ($difference > 0 && $difference <= self::DAYS_TO_REMIND_ABOUT_DATASET_UPDATE) {
            return 'reminder';
        }
        if ($difference > self::DAYS_TO_REMIND_ABOUT_DATASET_UPDATE) {
            return 'normal';
        }
        if ($difference <= 0) {
            return 'debtor';
        }

        return 'normal';
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
                $nextDatasetUpdateDate->addDay();
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

    public function all(Request $request) {
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
            ])
            ->get()->toArray();
        return response()->json($datasets);
    }

    /**
     * Receives unsorted array from database and makes it json-like view
     */
    private function sortDatasets($arrayOfDatasetsFromDB) {

    }
}

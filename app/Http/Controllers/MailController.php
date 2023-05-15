<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\DebtorNotification;
use App\Mail\ReminderNotification;
use App\Mail\DebtorReportNotification;
use App\Mail\ReminderReportNotification;
use Carbon\Carbon;

use App\Helpers\AuthApi;

class MailController extends Controller
{
    public $datasourceUrl = 'https://data.lutskrada.gov.ua';

    public function sendMail(Request $request) {
        $auth = AuthAPI::isAuthenticated($request->bearerToken(), $request->ip());
        $userId = $auth->user->id;

        $data = $request->validate([
            'mode' => ["required", "string", "regex:/^debtor$|^reminder$/i"],
        ]);

        $datasets = DB::table('datasets')
            ->select('*')
            ->orderBy('datasets.maintainer_email','asc')
            ->orderBy('datasets.title','asc')
            ->where([
                ['datasets.user_id', '=', $userId],
            ]);

        switch ($data["mode"]) {
            case "debtor":
                $datasets->where('type', '=', 'debtor');
                break;
            case "reminder":
                $datasets->where('type', '=', 'reminder');
                break;
            default:
                break;
        }

        $maintainers = $this->sortMaintainers($datasets->get()->toArray());
        $result = [
            "success" => [],
            "error" => [],
        ];

        foreach ($maintainers as $key => $maintainer) {
            $currentNotificator;

            if ($data["mode"] == 'debtor') {
                $currentNotificator = new DebtorNotification($maintainer, $this->datasourceUrl);
            }
            if ($data["mode"] == 'reminder') {
                $currentNotificator = new ReminderNotification($maintainer, $this->datasourceUrl);
            }

            try {
                Mail::to($maintainer["email"])->send($currentNotificator);
                $result["success"][] = $maintainer;
            } catch (\Symfony\Component\Mailer\Exception\TransportException $e) {
                $result["error"][] = $maintainer;
            }
        }

        if ($data["mode"] == 'debtor') {
            $reportNotificator = new DebtorReportNotification($result);
        }
        if ($data["mode"] == 'reminder') {
            $reportNotificator = new ReminderReportNotification($result);
        }

        Mail::to("asu@lutskrada.gov.ua")->send($reportNotificator);

        return response($result, 200);
    }

    /**
     * Receives unsorted array from 'datasets' table and makes it json-like view
     * (for emails sending)
     */
    private function sortMaintainers($arrayOfDatasetsFromDB) {
        $arrayOfDatasetsFromDB = json_decode(json_encode($arrayOfDatasetsFromDB), true);
        $previousMaintanerMail = null;
        $currentMaintanerMail = null;
        $currentMaintainerMailArrayIndex = -1;
        $previousDatasetTitle = null;
        $currentDatasetTitle = null;
        $currentDatasetTitleArrayIndex = -1;

        foreach ($arrayOfDatasetsFromDB as $key => $row) {
            $currentMaintanerMail = $row["maintainer_email"];
            $currentDatasetTitle = $row["title"];

            if ($currentMaintanerMail != $previousMaintanerMail) {
                $currentMaintainerMailArrayIndex += 1;
                $currentDatasetTitleArrayIndex = -1;
                $outputArray["maintainers"][$currentMaintainerMailArrayIndex] = [
                    'email' => trim($row["maintainer_email"]),
                    'name' => trim($row["maintainer_name"]),
                ];
            }

            if ($currentDatasetTitle != $previousDatasetTitle) {
                $currentDatasetTitleArrayIndex += 1;
                $nextUpdateDate = new Carbon($row["next_update_at"]);
                $nextUpdateDate = $nextUpdateDate->format('d.m.Y H:i');

                $outputArray
                    ["maintainers"][$currentMaintainerMailArrayIndex]
                    ["datasets"][$currentDatasetTitleArrayIndex] = [
                    'id' => $row['id'],
                    'title' => $row["title"],
                    'next_update_at' => $nextUpdateDate,
                    'update_frequency' => $row["update_frequency"],
                    'days_to_update' => $row["days_to_update"],
                ];
            }

            $previousMaintanerMail = $row["maintainer_email"];
            $previousDatasetTitle = $row["title"];
        }

        return $outputArray["maintainers"];
    }
}

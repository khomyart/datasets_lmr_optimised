<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

use App\Helpers\AuthApi;

class ReportController extends Controller
{
    public $datasourceUrl = 'https://data.lutskrada.gov.ua';
    const DAYS_TO_REMIND_ABOUT_DATASET_UPDATE = 7;

    public function getReport(Request $request) {
        $auth = AuthAPI::isAuthenticated($request->bearerToken(), $request->ip());
        $userId = $auth->user->id;

        $datasets = DB::table('datasets')
        ->select(
            'datasets.id as identifier',
            'datasets.title as title',
            'datasets.description as description',
            'datasets.update_frequency as accrualPeriodicity',
            'datasets.tags as keyword',
            'datasets.purpose as purpose',
            'datasets.type as type',
            'datasets.next_update_at as nextUpdateDate',
            'datasets.days_to_update as daysToUpdate',
            //landingPage
            'datasets.formats as distributionFormat',
            'executive_authorities.display_name as publisherPrefLabel',
            'executive_authorities.id as publisherIdentifier',
            'datasets.maintainer_name as contactPointFn',
            'datasets.maintainer_email as contactPointHasEmail',

        )
        ->leftJoin('executive_authorities', 'executive_authorities.name', '=', 'datasets.executive_authority_name')
        ->orderBy('datasets.title','asc')
        ->where([
            ['datasets.user_id', '=', $userId],
            ['executive_authorities.user_id', '=', $userId],
        ])->get()->toArray();

        $dataForXlsx = [
            ["identifier", "title", "description", "accrualPeriodicity", "keyword", "purpose", "landingPage", "distributionFormat", "publisherPrefLabel", "publisherIdentifier", "contactPointFn", "contactPointHasEmail", "|", "type", "nextUpdateDate", "daysToUpdate"],
            ["Ідентифікатор", "Назва", "Опис", "Частота оновлень", "Ключові слова", "Підстава та призначення збору інформації", "Посилання на сторінку набору даних", "Формати ресурсів", "Назва розпорядника", "Ідентифікатор розпорядника", "Відповідальна особа", "Email відповідальної особи", "|", "Тип", "Наступна дата оновлення", "Днів до оновлення"],
        ];

        $datasets = json_decode(json_encode($datasets), true);

        foreach ($datasets as $key => $dataset) {
            $dataForXlsx[] = [
                $dataset["identifier"],
                $dataset["title"],
                $dataset["description"],
                $this->translateDatasetUpdateFrequency($dataset["accrualPeriodicity"]),
                $dataset["keyword"],
                $dataset["purpose"],
                "{$this->datasourceUrl}/dataset/{$dataset["identifier"]}",
                $dataset["distributionFormat"],
                $dataset["publisherPrefLabel"],
                $dataset["publisherIdentifier"],
                $dataset["contactPointFn"],
                $dataset["contactPointHasEmail"],
                "|",
                $dataset["type"],
                $dataset["nextUpdateDate"],
                $dataset["daysToUpdate"],
            ];
        }

        $spreadsheet = new Spreadsheet();
        $activeWorksheet = $spreadsheet
        ->getActiveSheet()
        ->fromArray($dataForXlsx, null, 'A1');

        $currentTimestamp = Carbon::now('Europe/Kyiv')->timestamp;
        $writer = new Xlsx($spreadsheet);
        $writer->save("./../storage/app/reports/datasets_{$currentTimestamp}.xlsx");
        $filePath = Storage::disk('local')->path("/reports/datasets_{$currentTimestamp}.xlsx");

        //remove report files from storage by running python script
        $absolutePathToScript = Storage::disk('local')->path("reports_cleaner.py");
        $cmd = "py {$absolutePathToScript} 2>&1";
        exec($cmd);

        $response = response()->download($filePath, 'report');
        return $response;
    }

    /**
     * gets english version of 'update frequency' label
     * and translates it on ukrainian
     */
    private function translateDatasetUpdateFrequency($updateFrequency) {
        $accrualPeriodicity = "";
        switch ($updateFrequency) {
          case "immediately after making changes":
            $accrualPeriodicity = "одразу після внесення змін";
            break;
          case "more than once a day":
            $accrualPeriodicity = "більше одного разу на день";
            break;
          case "once a day":
            $accrualPeriodicity = "щодня";
            break;
          case "once a week":
            $accrualPeriodicity = "щотижня";
            break;
          case "once a month":
            $accrualPeriodicity = "щомісяця";
            break;
          case "once a quarter":
            $accrualPeriodicity = "щокварталу";
            break;
          case "once a half year":
            $accrualPeriodicity = "щопівроку";
            break;
          case "once a year":
            $accrualPeriodicity = "раз на рік";
            break;
          case "no longer updated":
            $accrualPeriodicity = "більше не оновлюється";
            break;
          default:
            break;
        }

        return $accrualPeriodicity;
    }
}

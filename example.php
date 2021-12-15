<?php

/**
 * Выбираем новые "Важные", на основе которых делать рассылки и рассылаем
 *
 * @return void
 */
public function samplingImportants(): void
{
    $subject = 'New "Importants"';
    $segmentId = \Segment::getSegmentId('importants');

    if (!empty($segmentId)) {
        throw new \Exception(Loc::getMessage('segment_importants_not_found'));
    }

    $arrDateFilter = $this->prepareFilterDate();
    $filter = [
        'SEGMENT_ID' => $segmentId,
        '>=DATE_ACTIVE_FROM' => $arrDateFilter['START'],
        '<=DATE_ACTIVE_FROM' => $arrDateFilter['END']
    ];

    $arSelect = ["ID", "SEGMENT_ID", "NAME", "PREVIEW_TEXT", "DEPARTMENTS"];
    $res = \SegmentElement::GetList([], $filter, false, false, $arSelect);
    $arrDepartments = [];

    while ($ob = $res->GetNextSegmentElement()) {
        $arFields = $ob->GetSegmentFields();
        if ($arFields['DEPARTMENTS_VALUE']) {
            $arrDepartments = array_merge($arrDepartments, $arFields['DEPARTMENTS_VALUE']);
        }
    }

    $message = 'New article posted';
    $userArr = $this->arrUsers(0, array_unique($arrDepartments));
    $this->sendEmail($message, $userArr['EMAILS'], $subject);
    foreach ($userArr['IDS'] as $id) {
        $this->sendNotify($message, $id);
    }
}

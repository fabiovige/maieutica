<?php

namespace App\Interfaces\Services;

interface KidServiceInterface
{
    public function getKidDetails($kidId);
    public function createKid(array $data);
    public function updateKid($kid, array $data);
    public function deleteKid($kid);
    public function generatePdfPlane($planeId);
    public function generatePdfPlaneAuto($kidId, $checklistId, $note);
    public function getRadarChartData($kidId, $levelId, $checklistId = null);
    public function getDomainDetailsData($kidId, $levelId, $domainId, $checklistId = null);
}

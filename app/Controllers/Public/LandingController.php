<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Models\ReportModel;

class LandingController extends BaseController
{
    public function index(): string
    {
        $reportModel = new ReportModel();

        return $this->render('layouts/public', 'public/landing', [
            'stats'   => $reportModel->getStats(),
            'mapLat'  => $this->setting('map_center_lat',  '-6.2884'),
            'mapLng'  => $this->setting('map_center_long', '106.7135'),
            'mapZoom' => $this->setting('map_default_zoom', '12'),
            'geoJson' => $this->setting('city_boundary_geojson', ''),
        ]);
    }

    public function map(): string
    {
        return $this->render('layouts/public', 'public/map', [
            'mapLat'  => $this->setting('map_center_lat',  '-6.2884'),
            'mapLng'  => $this->setting('map_center_long', '106.7135'),
            'mapZoom' => $this->setting('map_default_zoom', '12'),
            'geoJson' => $this->setting('city_boundary_geojson', ''),
        ]);
    }

    /** Public GeoJSON endpoint – only cleaned/active reports, no personal data. */
    public function geojson()
    {
        $reportModel = new ReportModel();
        $status      = $this->request->getGet('status');
        $collection  = $reportModel->toGeoJson($status ?: null);

        return $this->response
            ->setContentType('application/json')
            ->setJSON($collection);
    }
}

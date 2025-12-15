<?php

namespace App\Http\Controllers\MAD\Services;

class MADKPIService
{
    protected object $request;
    protected int $year;
    protected array $monthes;
    protected array $countries;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function getKPI()
    {
        $this->resolveYear();

        return [
            'year' => $this->year,
        ];
    }

    protected function resolveYear()
    {
        $this->year = $this->request->input('year', date('Y'));
    }
}

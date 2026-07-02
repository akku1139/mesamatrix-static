<?php

declare(strict_types=1);

/*
 * This file is part of mesamatrix.
 *
 * Copyright (C) 2014-2022 Romain "Creak" Failliot.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Mesamatrix\Controller;

use DateTime;
use Mesamatrix\Mesamatrix;
use Mesamatrix\Donation\Contributor;
use Mesamatrix\Donation\YearContributors;

class DonateController extends BaseController
{
    /** @var array<string, YearContributors> */
    private $yearsContributors = array();

    public function __construct()
    {
        parent::__construct();

        $this->setPage('Donate?');

        $this->addJsScript('js/script.js');
    }

    /**
     * Load contributors' donations.
     *
     * Sorted by year and by donation descending.
     */
    private function loadContributors(): void
    {
        $contribsPath = Mesamatrix::path(Mesamatrix::$config->getValue('info', 'private_dir') .
            "/contributors.json");
        if (!file_exists($contribsPath)) {
            return;
        }

        $contribsContents = file_get_contents($contribsPath);
        if ($contribsContents !== false) {
            $contribs = json_decode($contribsContents);

            foreach ($contribs as &$jsonContributor) {
                $date = new DateTime($jsonContributor->date);
                $year = $date->format("Y");

                $contributor = new Contributor();
                $contributor->name = $jsonContributor->name;
                $contributor->date = $date;
                $contributor->donation = $jsonContributor->donation;

                if (!array_key_exists($year, $this->yearsContributors)) {
                    $yearContributors = new YearContributors();
                    $yearContributors->year = $year;
                    $this->yearsContributors[$year] = $yearContributors;
                } else {
                    $yearContributors = $this->yearsContributors[$year];
                }

                $yearContributors->contributors[] = $contributor;
                $yearContributors->total += $contributor->donation;
            }

            // Add current year, if not there yet.
            $currentYear = (new DateTime())->format("Y");
            if (!array_key_exists($currentYear, $this->yearsContributors)) {
                $yearContributor = new YearContributors();
                $yearContributor->year = $currentYear;
                $this->yearsContributors[$currentYear] = $yearContributor;
            }

            // For each year, sort by donation desc.
            foreach ($this->yearsContributors as &$yearContributor) {
                $contributors = &$yearContributor->contributors;
                usort($contributors, function ($a, $b) {
                    $diff = $b->donation - $a->donation;
                    return $diff <=> 0.0;
                });
            }

            // Sort by year desc.
            krsort($this->yearsContributors);
        }
    }

    protected function computeRendering(): void
    {
        $this->loadContributors();
    }

    protected function writeHtmlPage(): void
    {
        echo <<<'HTML'
    <h1>Thinking about donating?</h1>
    <p>Thanks for the thought! However, Mesamatrix-Static is powered by free tiers on GitHub and Cloudflare.</p>
    <p>Please direct your generosity to <a href="https://mesamatrix.net/donate.php">Mesamatrix.net</a> instead.</p>
HTML;
    }
}

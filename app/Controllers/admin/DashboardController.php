<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();

        $leadStats = [
            'total'             => 0,
            'new'               => 0,
            'follow_up'         => 0,
            'hot'               => 0,
            'converted'         => 0,
            'follow_up_today'   => 0,
            'overdue'           => 0,
            'unassigned'        => 0,
            'this_month'        => 0,
            'conversion_rate'   => 0,
        ];

        $latestLeads       = [];
        $upcomingFollowUps = [];
        $monthlyRows       = [];
        $packageRows       = [];
        $pipelineRows      = [];

        if ($db->tableExists('travel_leads')) {
            $leadStats['total'] = $db->table('travel_leads')
                ->where('deleted_at', null)
                ->countAllResults();

            $leadStats['new'] = $db->table('travel_leads')
                ->where('status', 'new')
                ->where('deleted_at', null)
                ->countAllResults();

            $leadStats['follow_up'] = $db->table('travel_leads')
                ->whereIn('status', ['contacted', 'follow_up', 'qualified', 'waiting_decision'])
                ->where('deleted_at', null)
                ->countAllResults();

            $leadStats['hot'] = $db->table('travel_leads')
                ->where('lead_temperature', 'hot')
                ->where('deleted_at', null)
                ->countAllResults();

            $leadStats['converted'] = $db->table('travel_leads')
                ->where('status', 'converted')
                ->where('deleted_at', null)
                ->countAllResults();

            $leadStats['follow_up_today'] = $db->table('travel_leads')
                ->where('deleted_at', null)
                ->where('next_follow_up_at >=', date('Y-m-d 00:00:00'))
                ->where('next_follow_up_at <=', date('Y-m-d 23:59:59'))
                ->countAllResults();

            $leadStats['overdue'] = $db->table('travel_leads')
                ->where('deleted_at', null)
                ->where('next_follow_up_at <', date('Y-m-d H:i:s'))
                ->whereNotIn('status', ['converted', 'not_interested', 'cancelled'])
                ->countAllResults();

            $leadStats['unassigned'] = $db->table('travel_leads')
                ->where('deleted_at', null)
                ->where('assigned_admin_id', null)
                ->countAllResults();

            $leadStats['this_month'] = $db->table('travel_leads')
                ->where('deleted_at', null)
                ->where('created_at >=', date('Y-m-01 00:00:00'))
                ->countAllResults();

            $leadStats['conversion_rate'] = $leadStats['total'] > 0
                ? round(($leadStats['converted'] / $leadStats['total']) * 100, 1)
                : 0;

            $latestLeads = $db->table('travel_leads')
                ->select('travel_leads.*, packages.name AS package_name, users.name AS assigned_admin_name')
                ->join('packages', 'packages.id = travel_leads.package_id', 'left')
                ->join('users', 'users.id = travel_leads.assigned_admin_id', 'left')
                ->where('travel_leads.deleted_at', null)
                ->orderBy('travel_leads.id', 'DESC')
                ->limit(7)
                ->get()
                ->getResultArray();

            $upcomingFollowUps = $db->table('travel_leads')
                ->select('travel_leads.*, packages.name AS package_name, users.name AS assigned_admin_name')
                ->join('packages', 'packages.id = travel_leads.package_id', 'left')
                ->join('users', 'users.id = travel_leads.assigned_admin_id', 'left')
                ->where('travel_leads.deleted_at', null)
                ->where('travel_leads.next_follow_up_at IS NOT NULL', null, false)
                ->whereNotIn('travel_leads.status', ['converted', 'not_interested', 'cancelled'])
                ->orderBy('travel_leads.next_follow_up_at', 'ASC')
                ->limit(6)
                ->get()
                ->getResultArray();

            $monthlyRows = $db->table('travel_leads')
                ->select("DATE_FORMAT(created_at, '%Y-%m') AS month_key, DATE_FORMAT(created_at, '%b %Y') AS month_label, COUNT(*) AS total", false)
                ->where('deleted_at', null)
                ->where('created_at >=', date('Y-m-01 00:00:00', strtotime('-5 months')))
                ->groupBy(['month_key', 'month_label'])
                ->orderBy('month_key', 'ASC')
                ->get()
                ->getResultArray();

            $packageRows = $db->table('travel_leads')
                ->select('COALESCE(packages.name, \'Tanpa Paket\') AS package_name, COUNT(travel_leads.id) AS total', false)
                ->join('packages', 'packages.id = travel_leads.package_id', 'left')
                ->where('travel_leads.deleted_at', null)
                ->groupBy(['travel_leads.package_id', 'packages.name'])
                ->orderBy('total', 'DESC')
                ->limit(6)
                ->get()
                ->getResultArray();

            $pipelineRows = $db->table('travel_leads')
                ->select('status, COUNT(*) AS total', false)
                ->where('deleted_at', null)
                ->groupBy('status')
                ->get()
                ->getResultArray();
        }

        $packageStats = [
            'total'       => 0,
            'active'      => 0,
            'departures'  => 0,
            'trash'       => 0,
        ];

        if ($db->tableExists('packages')) {
            $packageStats['total'] = $db->table('packages')
                ->where('deleted_at', null)
                ->countAllResults();
            $packageStats['active'] = $db->table('packages')
                ->where('deleted_at', null)
                ->where('status', 'active')
                ->countAllResults();
            $packageStats['trash'] = $db->table('packages')
                ->where('deleted_at IS NOT NULL', null, false)
                ->countAllResults();
        }

        if ($db->tableExists('package_departures')) {
            $packageStats['departures'] = $db->table('package_departures')
                ->where('status', 'available')
                ->where('departure_date >=', date('Y-m-d'))
                ->countAllResults();
        }

        return view('admin/dashboard/index', [
            'title'              => 'Dashboard Admin - ' . site_setting('site_name', 'Travel Umroh & Haji'),
            'leadStats'          => $leadStats,
            'packageStats'       => $packageStats,
            'latestLeads'        => $latestLeads,
            'upcomingFollowUps'  => $upcomingFollowUps,
            'monthlyRows'        => $monthlyRows,
            'packageRows'        => $packageRows,
            'pipelineRows'       => $pipelineRows,
        ]);
    }
}

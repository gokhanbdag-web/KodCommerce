<?php
declare(strict_types=1);

namespace KodCommerce\Support;

final class Navigation
{
    /** Central registry: one slug per AJAX and direct-load destination. */
    public static function groups(): array
    {
        return [
            'Dashboard' => ['Overview'],
            'CRM' => ['Customers', 'Leads', 'Opportunities', 'Activities'],
            'Inventory & Warehouse' => ['Inventory', 'Warehouses', 'Stock Movements', 'Stock Counts'],
            'Sales & Purchasing' => ['Sales', 'Purchases', 'Quotations', 'Suppliers'],
            'Accounting & Finance' => ['Accounts', 'Transactions', 'Invoices', 'Payments', 'Reports'],
            'POS' => ['Point of Sale', 'POS Sessions'],
            'Manufacturing' => ['Production Orders', 'Bill of Materials'],
            'Technical Service' => ['Service Tickets'],
            'Shipping & Returns' => ['Shipments', 'Returns'],
            'Human Resources' => ['Employees', 'Attendance'],
            'Branch Management' => ['Branches', 'Branch Staff', 'Branch Inventory'],
            'E-Commerce' => ['Products', 'Categories', 'Orders', 'Customers'],
            'Marketplace Integrations' => ['Trendyol', 'Hepsiburada', 'Ciceksepeti', 'Pazarama', 'PTTAVM'],
            'Content Management' => ['Pages'],
            'Theme Management' => ['Header Management', 'Hero Management', 'Footer Management', 'Typography', 'Colors'],
            'Menu Management' => ['Menus'],
            'Blog Management' => ['Posts', 'Blog Categories', 'Tags'],
            'Media Management' => ['Media Library'],
            'Site Settings' => ['General Settings', 'Writing', 'Reading', 'Discussion', 'Media Settings', 'Permalinks', 'Privacy', 'Mail Settings', 'Maintenance & Cache'],
            'User Management' => ['Users', 'Roles & Permissions', 'My Profile'],
            'Integrations' => ['Integrations'],
            'Analytics & Reporting' => ['Analytics', 'Reports'],
        ];
    }

    public static function slug(string $label): string
    {
        return trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower($label)), '-');
    }

    public static function pages(): array
    {
        $pages = [];
        foreach (self::groups() as $group => $labels) {
            foreach ($labels as $label) {
                $pages[self::slug($group) . '/' . self::slug($label)] = [$group, $label];
            }
        }
        return $pages;
    }
}

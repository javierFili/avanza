<?php

namespace Webkul\Installer\Database\Seeders\User;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @param  array  $parameters
     * @return void
     */
    public function run($parameters = [])
    {
        DB::table('users')->delete();

        DB::table('roles')->delete();

        $defaultLocale = $parameters['locale'] ?? config('app.locale');

        DB::table('roles')->insert([
            'id'              => 1,
            'name'            => trans('installer::app.seeders.user.role.administrator', [], $defaultLocale),
            'description'     => trans('installer::app.seeders.user.role.administrator-role', [], $defaultLocale),
            'permission_type' => 'all',
        ]);
        DB::table('roles')->insert([
            'id'              => 2,
            'name'            =>'Administrador General',
            'description'     => 'Administrador general del cliente asociado al CRM',
            'permission_type' => '["goals","goals.create","goals.edit","goals.delete","dashboard","leads","leads.create","leads.view","leads.edit","leads.delete","quotes","quotes.create","quotes.edit","quotes.print","quotes.delete","mail","mail.inbox","mail.draft","mail.outbox","mail.sent","mail.trash","mail.compose","mail.view","mail.edit","mail.delete","activities","activities.create","activities.edit","activities.delete","contacts","contacts.persons","contacts.persons.create","contacts.persons.edit","contacts.persons.delete","contacts.persons.view","contacts.organizations","contacts.organizations.create","contacts.organizations.edit","contacts.organizations.delete","products","products.create","products.edit","products.delete","products.view","settings","settings.lead","settings.lead.pipelines","settings.lead.pipelines.create","settings.lead.pipelines.edit","settings.lead.pipelines.delete","settings.lead.pipelines.view","settings.lead.sources","settings.lead.sources.create","settings.lead.sources.edit","settings.lead.sources.delete","settings.lead.types","settings.lead.types.create","settings.lead.types.edit","settings.lead.types.delete","settings.automation","settings.automation.attributes","settings.automation.attributes.create","settings.automation.attributes.edit","settings.automation.attributes.delete","settings.automation.webhooks","settings.automation.webhooks.create","settings.automation.webhooks.edit","settings.automation.webhooks.delete","settings.automation.workflows","settings.automation.workflows.create","settings.automation.workflows.edit","settings.automation.workflows.delete","settings.automation.events","settings.automation.events.create","settings.automation.events.edit","settings.automation.events.delete","settings.automation.campaigns","settings.automation.campaigns.create","settings.automation.campaigns.edit","settings.automation.campaigns.delete","settings.automation.email_templates","settings.automation.email_templates.create","settings.automation.email_templates.edit","settings.automation.email_templates.delete","settings.data_transfer","settings.data_transfer.imports","settings.data_transfer.imports.create","settings.data_transfer.imports.edit","settings.data_transfer.imports.delete","settings.data_transfer.imports.import","configuration"]',
        ]);
        DB::table('roles')->insert([
            'id'              => 3,
            'name'            => 'Agente de Ventas',
            'description'     => 'Agente de ventas del CRM',
            'permission_type' => '["dashboard","leads","leads.create","leads.view","leads.edit","leads.delete","quotes","quotes.create","quotes.edit","quotes.print","quotes.delete","mail","mail.inbox","mail.draft","mail.outbox","mail.sent","mail.trash","mail.compose","mail.view","mail.edit","mail.delete","activities","activities.create","activities.edit","activities.delete","contacts","contacts.persons","contacts.persons.create","contacts.persons.edit","contacts.persons.delete","contacts.persons.view","contacts.organizations","contacts.organizations.create","contacts.organizations.edit","contacts.organizations.delete","products","products.create","products.edit","products.delete","products.view"]',
        ]);
    }
}

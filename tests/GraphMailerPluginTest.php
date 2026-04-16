<?php

use APP\plugins\generic\graphMailer\GraphMailerPlugin;
use Illuminate\Mail\MailManager;
use Illuminate\Support\Facades\Mail;
use InnoGE\LaravelMsGraphMail\MicrosoftGraphTransport;
use PKP\config\Config;
use PKP\core\PKPContainer;
use Tests\Support\ArrayConfigRepository;
use Tests\Support\TestContainer;

function resetTestState(): array
{
    Config::reset();

    $mailManager = new MailManager();
    Mail::swap($mailManager);

    $config = new ArrayConfigRepository();
    PKPContainer::setInstance(new TestContainer([
        'config' => $config,
    ]));

    return [$mailManager, $config];
}

function testMetadataMethods(): void
{
    $plugin = new GraphMailerPlugin();

    assertTrue($plugin->isSitePlugin());
    assertSame('Graph Mailer', $plugin->getDisplayName());
    assertSame('Switch the default mail driver to Microsoft Graph.', $plugin->getDescription());
}

function testEnableDisableFollowsSiteContext(): void
{
    $plugin = new GraphMailerPlugin();

    $plugin->currentContextId = 0;
    assertTrue($plugin->getCanEnable());
    assertTrue($plugin->getCanDisable());

    $plugin->currentContextId = 3;
    assertFalse($plugin->getCanEnable());
    assertFalse($plugin->getCanDisable());
}

function testRegisterConfiguresGraphMailerWhenEnabled(): void
{
    [$mailManager, $config] = resetTestState();

    Config::setVar('email', 'azure_tenant_id', 'tenant-id');
    Config::setVar('email', 'azure_client_id', 'client-id');
    Config::setVar('email', 'azure_client_secret', 'client-secret');

    $plugin = new GraphMailerPlugin();
    $plugin->enabled = true;
    $plugin->parentRegisterResult = true;

    assertTrue($plugin->register('generic', 'graphMailer'));
    assertSame('microsoft-graph', $mailManager->defaultDriver);
    assertArrayHasKey('microsoft-graph', $mailManager->extensions);

    $transport = $mailManager->extensions['microsoft-graph']([]);
    assertInstanceOf(MicrosoftGraphTransport::class, $transport);
    assertSame('tenant-id', $transport->service->config['tenantId']);
    assertSame('client-id', $transport->service->config['clientId']);
    assertSame('client-secret', $transport->service->config['clientSecret']);
    assertSame(3000, $transport->service->config['accessTokenTtl']);
    assertSame([
        'microsoft-graph' => [
            'transport' => 'microsoft-graph',
            'save_to_sent_items' => true,
        ],
    ], $config->get('mail.mailers', []));
}

function testRegisterSkipsMailerSwitchWhenPluginDisabled(): void
{
    [$mailManager, $config] = resetTestState();

    $plugin = new GraphMailerPlugin();
    $plugin->enabled = false;
    $plugin->parentRegisterResult = true;

    assertTrue($plugin->register('generic', 'graphMailer'));
    assertSame(null, $mailManager->defaultDriver);
    assertSame([], $mailManager->extensions);
    assertSame([], $config->get('mail.mailers', []));
}

function testRegisterSkipsMailerSwitchWhenParentRegisterFails(): void
{
    [$mailManager, $config] = resetTestState();

    $plugin = new GraphMailerPlugin();
    $plugin->enabled = true;
    $plugin->parentRegisterResult = false;

    assertFalse($plugin->register('generic', 'graphMailer'));
    assertSame(null, $mailManager->defaultDriver);
    assertSame([], $mailManager->extensions);
    assertSame([], $config->get('mail.mailers', []));
}

return [
    'metadata methods' => 'testMetadataMethods',
    'enable/disable site context rules' => 'testEnableDisableFollowsSiteContext',
    'enabled register configures graph mailer' => 'testRegisterConfiguresGraphMailerWhenEnabled',
    'disabled register skips graph mailer' => 'testRegisterSkipsMailerSwitchWhenPluginDisabled',
    'failed parent register skips graph mailer' => 'testRegisterSkipsMailerSwitchWhenParentRegisterFails',
];

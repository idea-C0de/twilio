<?php

namespace NotificationChannels\Twilio\Test;

use Mockery;
use ArrayAccess;
use Twilio\Rest\Client as TwilioService;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use NotificationChannels\Twilio\Twilio;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioConfig;
use NotificationChannels\Twilio\TwilioProvider;
use Illuminate\Contracts\Foundation\Application;

class TwilioProviderTest extends MockeryTestCase
{
    /** @var TwilioProvider */
    protected $provider;

    /** @var App */
    protected $app;

    public function setUp()
    {
        parent::setUp();

        $this->app = Mockery::mock(App::class);
        $this->provider = new TwilioProvider($this->app);
    }

    /** @test */
    public function it_gives_an_instantiated_twilio_object_when_the_channel_asks_for_it()
    {
        $configArray = [
            'account_sid' => 'sid',
            'auth_token' => 'token',
            'from' => 'from',
        ];

        $this->app->shouldReceive('offsetGet')
            ->with('config')
            ->andReturn([
                'services.twilio' => $configArray,
            ]);

        $twilio = Mockery::mock(TwilioService::class);
        $config = Mockery::mock(TwilioConfig::class, $configArray);

        $this->app->shouldReceive('make')->with(TwilioConfig::class)->andReturn($config);
        $this->app->shouldReceive('make')->with(TwilioService::class)->andReturn($twilio);

        $this->app->shouldReceive('when')->with(TwilioChannel::class)->once()->andReturn($this->app);
        $this->app->shouldReceive('needs')->with(Twilio::class)->once()->andReturn($this->app);
        $this->app->shouldReceive('give')->with(Mockery::on(function ($twilio) {
            return $twilio() instanceof Twilio;
        }))->once();

        $this->app->shouldReceive('bind')->with(TwilioService::class, Mockery::on(function ($twilio) {
            return  $twilio() instanceof TwilioService;
        }))->once()->andReturn($this->app);

        $this->provider->boot();
    }
}

interface App extends Application, ArrayAccess
{
}

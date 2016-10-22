<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Services\SigaService;
use App\Repositories\SigaInterface;

class SigaServiceTest extends TestCase
{
    public function testGetProfileById()
    {
        $mockResult = array('profile');

        $sigaRepositoryMock = Mockery::mock(SigaInterface::class);
        $sigaRepositoryMock->shouldReceive('getProfileById')->with('123')->once()->andReturn($mockResult);

        $siga = new SigaService($sigaRepositoryMock);
        $result = $siga->getProfileById('123');
        $this->assertEquals($mockResult, $result);
    }
}

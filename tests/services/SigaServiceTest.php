<?php

namespace Tests;

use Caronae\Services\SigaService;
use Caronae\Repositories\SigaInterface;
use Caronae\Exceptions\SigaException;
use Mockery;

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

    public function testGetProfilePictureById()
    {
        $mockResult = new \stdClass;
        $mockResult->urlFoto = 'foto';

        $sigaRepositoryMock = Mockery::mock(SigaInterface::class);
        $sigaRepositoryMock->shouldReceive('getProfileById')->with('123')->once()->andReturn($mockResult);

        $siga = new SigaService($sigaRepositoryMock);
        $result = $siga->getProfilePictureById('123');
        $this->assertEquals('foto', $result);
    }

    public function testGetProfilePictureByIdNotFound()
    {
        $this->setExpectedException(SigaException::class);

        $mockResult = new \stdClass;
        $mockResult->urlFoto = '';

        $sigaRepositoryMock = Mockery::mock(SigaInterface::class);
        $sigaRepositoryMock->shouldReceive('getProfileById')->with('123')->once()->andReturn($mockResult);

        $siga = new SigaService($sigaRepositoryMock);
        $result = $siga->getProfilePictureById('123');
    }
}

<?php

use App\Services\SigaService;
use App\Repositories\SigaInterface;
use App\Exceptions\SigaException;

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
        $mockResult = new stdClass;
        $mockResult->urlFoto = '146.164.2.117:8090/image.jpg';

        $sigaRepositoryMock = Mockery::mock(SigaInterface::class);
        $sigaRepositoryMock->shouldReceive('getProfileById')->with('123')->once()->andReturn($mockResult);

        $siga = new SigaService($sigaRepositoryMock);
        $result = $siga->getProfilePictureById('123');
        $this->assertEquals('https://api.caronae.ufrj.br/user/intranetPhoto/image.jpg', $result);
    }

    public function testGetProfilePictureByIdNotFound()
    {
        $this->setExpectedException(SigaException::class);

        $mockResult = new stdClass;
        $mockResult->urlFoto = '';

        $sigaRepositoryMock = Mockery::mock(SigaInterface::class);
        $sigaRepositoryMock->shouldReceive('getProfileById')->with('123')->once()->andReturn($mockResult);

        $siga = new SigaService($sigaRepositoryMock);
        $result = $siga->getProfilePictureById('123');
    }
}

<?php

use ANDS\DOI\Model\Doi;
use ANDS\DOI\Repository\DoiRepository;
use Dotenv\Dotenv;

class DoiRepositoryTest extends PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_should_be_able_to_get_a_doi()
    {
        $repo = $this->getDoiRepository();

        $this->assertNotNull($repo->getFirst());
    }

    /** @test */
    public function it_should_be_able_to_get_a_doi_via_id()
    {
        $repo = $this->getDoiRepository();
        $doi = $repo->getByID('10.5072/00/53ED646B7A9A6');
        $this->assertNotNull($doi);
        $this->assertSame($doi->doi_id, '10.5072/00/53ED646B7A9A6');
        $this->assertEquals($doi->publisher, "ANDS");
    }


    /**
     * Helper method to return a new DoiRepository for each test
     *
     * @return DoiRepository
     */
    private function getDoiRepository() {
        $dotenv = new Dotenv('./');
        $dotenv->load();
        $repo = new DoiRepository(
            getenv("DATABASE_URL"),
            'dbs_dois',
            getenv("DATABASE_USERNAME"),
            getenv("DATABASE_PASSWORD")
        );
        return $repo;
    }
}
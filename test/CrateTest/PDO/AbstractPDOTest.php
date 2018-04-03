<?php


namespace CrateTest\PDO;

use Crate\PDO\PDO;
use PHPUnit\Framework\TestCase;

abstract class AbstractPDOTest extends TestCase
{
    /**
     * @var PDO
     */
    protected $pdo;

    protected function setUp()
    {
        $this->pdo = new PDO('crate:localhost:4200', 'crate', 'crate');
        $this->pdo->setAttribute(PDO::CRATE_ATTR_SSL_MODE, PDO::CRATE_ATTR_SSL_MODE_ENABLED_BUT_WITHOUT_HOST_VERIFICATION);

        $usr = $this->pdo->prepare("CREATE USER test_user WITH (password = 'pwd')");

        $usr->execute();

        $priv = "GRANT ALL PRIVILEGES TO test_user;";
        $this->pdo->query($priv);
    }

    protected function tearDown()
    {
        $del = "DROP USER IF EXISTS test_user";
        $this->pdo->query($del);
    }


}
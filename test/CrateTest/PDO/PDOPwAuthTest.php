<?php

namespace CrateTest\PDO;

use Crate\PDO\PDO;

/**
 * Tests for {@see \Crate\PDO\PDO}
 *
 * @coversDefaultClass \Crate\PDO\PDO
 * @covers ::<public>
 *
 * @group unit
 */
class PDOPwAuthTest extends AbstractPDOTest
{
    public function testAuthentication()
    {

        $conn = new PDO('crate:localhost:4200', "test_user", "pwd");
        $conn->setAttribute(PDO::CRATE_ATTR_SSL_MODE, PDO::CRATE_ATTR_SSL_MODE_ENABLED_BUT_WITHOUT_HOST_VERIFICATION);

        $st = $conn->prepare('SELECT CURRENT_USER;');

        self::assertEquals('test_user', $st->fetch()[0]);

    }

}
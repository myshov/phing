<?php
/*
 *  $Id$
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */

require_once 'phing/BuildFileTest.php';

/**
 * Regression test for tickets
 * http://www.phing.info/trac/ticket/524
 *
 * @package phing.regression
 * TODO: skip when user doesn't have pear installed (you cannot check for the class name, because
 *       it is included via composer)
 */
class PearPkg2CompatibilityTest extends BuildFileTest
{
    private $savedErrorLevel;
    protected $backupGlobals = false;

    public function setUp()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped("PEAR tests do not run on HHVM");
        }

        $this->savedErrorLevel = error_reporting();
        error_reporting(E_ERROR);
        $buildFile = PHING_TEST_BASE . "/etc/regression/524/build.xml";
        $this->configureProject($buildFile);
        $this->executeTarget("setup");
    }

    public function tearDown()
    {
        error_reporting($this->savedErrorLevel);
        $this->executeTarget("teardown");
    }

    protected function assertPreConditions()
    {
        try {
            $this->executeTarget("inactive");
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Unknown channel') !== false) {
                $this->markTestSkipped($e->getMessage());
            }
        }
    }

    public function testInactiveMaintainers()
    {
        $this->executeTarget("inactive");
        $content = file_get_contents(PHING_TEST_BASE . '/etc/regression/524/out/package2.xml');
        $this->assertTrue(strpos($content, '<active>no</active>') !== false);
    }

    public function testActiveMaintainers()
    {
        $this->executeTarget("active");
        $content = file_get_contents(PHING_TEST_BASE . '/etc/regression/524/out/package2.xml');
        $this->assertTrue(strpos($content, '<active>yes</active>') !== false);
    }

    public function testNotSetMaintainers()
    {
        $this->executeTarget("notset");
        $content = file_get_contents(PHING_TEST_BASE . '/etc/regression/524/out/package2.xml');
        $this->assertTrue(strpos($content, '<active>yes</active>') !== false);
    }
}

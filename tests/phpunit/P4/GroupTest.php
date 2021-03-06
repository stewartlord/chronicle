<?php
/**
 * Test methods for the P4 Group class.
 *
 * @copyright   2011 Perforce Software. All rights reserved.
 * @license     Please see LICENSE.txt in top-level folder of this distribution.
 * @version     <release>/<patch>
 */
class P4_GroupTest extends TestCase
{
    /**
     * Test fetchAll method
     */
    public function testFetchAll()
    {
        $group = new P4_Group;
        $group->setId('test1')->addUser('user1')->save();
        $group = new P4_Group;
        $group->setId('test2')->addUser('user1')->save();
        $group = new P4_Group;
        $group->setId('test3')->addUser('user2')->addSubgroup('test2')->save();
        $group = new P4_Group;
        $group->setId('test4')->addUser('user3')->addSubgroup('test3')->save();
        $group = new P4_Group;
        $group->setId('test5')->addUser('user4')->addOwner('user5')->save();
        $group = new P4_Group;
        $group->setId('test6')->addSubgroup('test7')->save();
        $group = new P4_Group;
        $group->setId('test7%%')
              ->addSubgroup('test%%7')
              ->addUser('user1')
              ->addUser('user%%1')
              ->addOwner('user%%2')
              ->save();
        $names = array('test1', 'test2', 'test3', 'test4', 'test5', 'test6', 'test7%%');

        // Verify full fetchAll works ok
        $this->assertSame(
            $names,
            P4_Group::fetchAll()->invoke('getId'),
            'Expected fetch all to match'
        );

        // Verify fetch with made up option works
        $this->assertSame(
            $names,
            P4_Group::fetchAll(
                array('fooBar' => true)
            )->invoke('getId'),
            'Expected fetch all with made up option to match'
        );

        // Verify full FETCH_MAXIMUM works
        $this->assertSame(
            array_slice($names, 0, 3),
            P4_Group::fetchAll(
                array(P4_Group::FETCH_MAXIMUM => '3')
            )->invoke('getId'),
            'Expected fetch all with Maximum to match'
        );

        // Verify full FETCH_BY_MEMBER works for users
        $expected = array_slice($names, 0, 2);
        $expected[] = $names[6];
        $this->assertSame(
            $expected,
            P4_Group::fetchAll(
                array(P4_Group::FETCH_BY_MEMBER => 'user1')
            )->invoke('getId'),
            'Expected fetch all with member filter to match'
        );

        // Verify full FETCH_BY_MEMBER works for users with position specifiers
        $this->assertSame(
            array('test7%%'),
            P4_Group::fetchAll(
                array(P4_Group::FETCH_BY_MEMBER => 'user%%1')
            )->invoke('getId'),
            'Expected fetch all with member %% filter to match'
        );

        // Verify full FETCH_BY_MEMBER works for groups
        $this->assertSame(
            (array)'test6',
            P4_Group::fetchAll(
                array(P4_Group::FETCH_BY_MEMBER => 'test7')
            )->invoke('getId'),
            'Expected fetch all with sub-group member filter to match'
        );

        // Verify full FETCH_BY_MEMBER works for groups with position specifiers
        $this->assertSame(
            (array)'test7%%',
            P4_Group::fetchAll(
                array(P4_Group::FETCH_BY_MEMBER => 'test%%7')
            )->invoke('getId'),
            'Expected fetch all with sub-group member %% filter to match'
        );

        // Verify full FETCH_BY_MEMBER works for owners
        $this->assertSame(
            (array)'test5',
            P4_Group::fetchAll(
                array(P4_Group::FETCH_BY_MEMBER => 'user5')
            )->invoke('getId'),
            'Expected fetch all with owner member filter to match'
        );

        // Verify full FETCH_BY_MEMBER works for owners with position specifiers
        $this->assertSame(
            (array)'test7%%',
            P4_Group::fetchAll(
                array(P4_Group::FETCH_BY_MEMBER => 'user%%2')
            )->invoke('getId'),
            'Expected fetch all with owner member %% filter to match'
        );

        // Verify full FETCH_BY_MEMBER with FETCH_INDIRECT works
        $expected = array_slice($names, 0, 4);
        $expected[] = $names[6];
        $this->assertSame(
            $expected,
            P4_Group::fetchAll(
                array(
                    P4_Group::FETCH_BY_MEMBER   => 'user1',
                    P4_Group::FETCH_INDIRECT    => true
                )
            )->invoke('getId'),
            'Expected fetch all with indirect member filter to match'
        );

        // Verify full FETCH_BY_MEMBER with FETCH_INDIRECT and FETCH_MAXIMUM works
        $this->assertSame(
            array_slice($names, 0, 2),
            P4_Group::fetchAll(
                array(
                    P4_Group::FETCH_BY_MEMBER   => 'user1',
                    P4_Group::FETCH_INDIRECT    => true,
                    P4_Group::FETCH_MAXIMUM     => 2
                )
            )->invoke('getId'),
            'Expected fetch all with indirect member filter and maximum to match'
        );

        // Verify full FETCH_BY_NAME works
        $this->assertSame(
            array_slice($names, 0, 1),
            P4_Group::fetchAll(
                array(P4_Group::FETCH_BY_NAME => 'test1')
            )->invoke('getId'),
            'Expected fetch all with name filter to match'
        );
        // Verify full FETCH_BY_NAME works with position specifiers
        $this->assertSame(
            array($names[6]),
            P4_Group::fetchAll(
                array(P4_Group::FETCH_BY_NAME => 'test7%%')
            )->invoke('getId'),
            'Expected fetch all with name %% filter to match'
        );
    }

    /**
     * Test groups specs returned by fetchAll
     */
    public function testFetchAllSpecs()
    {
        $group = new P4_Group;
        $group->setId('test1')
              ->addSubgroup('sg1')
              ->addUser('user1')
              ->addUser('user2')
              ->addUser('user3')
              ->addOwner('user0')
              ->save();

        $result = P4_Group::fetchAll();
        $this->assertSame(
            1,
            $result->count(),
            "Expected fetch all returns 1 result"
        );

        // prepare expected spec values
        $expectedUsers     = array('user1', 'user2', 'user3');
        $expectedOwners    = array('user0');
        $expectedSubgroups = array('sg1');

        // Verify result contains correct spec
        $group = $result->current();
        $this->assertSame(
            $expectedUsers,
            $group->getUsers(),
            "Expected fetch all users spec"
        );
        $this->assertSame(
            $expectedSubgroups,
            $group->getSubgroups(),
            "Expected fetch all subgroups spec"
        );
        $this->assertSame(
            $expectedOwners,
            $group->getOwners(),
            "Expected fetch all owners spec"
        );

        // Verify result contains correct specs for filter by name
        $result = P4_Group::fetchAll(
            array(P4_Group::FETCH_BY_NAME => 'test1')
        );
        $this->assertSame(
            1,
            $result->count(),
            "Expected fetch all #2 returns 1 result"
        );

        // Verify result contains correct spec
        $group = $result->current();
        $this->assertSame(
            $expectedUsers,
            $group->getUsers(),
            "Expected fetch all #2 users spec"
        );
        $this->assertSame(
            $expectedSubgroups,
            $group->getSubgroups(),
            "Expected fetch all #2 subgroups spec"
        );
        $this->assertSame(
            $expectedOwners,
            $group->getOwners(),
            "Expected fetch all #2 owners spec"
        );
        
        // Verify result contains correct specs for filter by member
        $result = P4_Group::fetchAll(
            array(P4_Group::FETCH_BY_MEMBER => 'user2')
        );
        $this->assertSame(
            1,
            $result->count(),
            "Expected fetch all #3 returns 1 result"
        );

        // Verify result contains correct spec
        $group = $result->current();
        $this->assertSame(
            $expectedUsers,
            $group->getUsers(),
            "Expected fetch all #3 users spec"
        );
        $this->assertSame(
            $expectedSubgroups,
            $group->getSubgroups(),
            "Expected fetch all #3 subgroups spec"
        );
        $this->assertSame(
            $expectedOwners,
            $group->getOwners(),
            "Expected fetch all #3 owners spec"
        );

        // Verify that saving partialy altered and unpopulated spec will save all values
        $group = P4_Group::fetchAll(
            array(P4_Group::FETCH_BY_MEMBER => 'user2')
        )->current();

        $group->setUsers(array('user4'))
              ->save();

        // Verify result contains correct spec
        $group = P4_Group::fetch('test1');
        $this->assertSame(
            array('user4'),
            $group->getUsers(),
            "Expected fetch all #4 users spec"
        );
        $this->assertSame(
            $expectedSubgroups,
            $group->getSubgroups(),
            "Expected fetch all #4 subgroups spec"
        );
        $this->assertSame(
            $expectedOwners,
            $group->getOwners(),
            "Expected fetch all #4 owners spec"
        );
    }

    /**
     * Tests invalid options and option combos
     */
    public function testFetchAllBadOptions()
    {
        $tests = array(
            array(
                'title'     => __LINE__.' integer name filter',
                'options'   => array(P4_Group::FETCH_BY_NAME => 0),
                'exception' => 'Filter by Name expects a valid group id.'
            ),
            array(
                'title'     => __LINE__.' empty string name filter',
                'options'   => array(P4_Group::FETCH_BY_NAME => ""),
                'exception' => 'Filter by Name expects a valid group id.'
            ),
            array(
                'title'     => __LINE__.' invalid name filter',
                'options'   => array(P4_Group::FETCH_BY_NAME => "-test"),
                'exception' => 'Filter by Name expects a valid group id.'
            ),
            array(
                'title'     => __LINE__.' name filter with indirect option',
                'options'   =>  array(
                                    P4_Group::FETCH_BY_NAME => "test",
                                    P4_Group::FETCH_INDIRECT => true
                                ),
                'exception' => 'Filter by Name is not compatible with Fetch by Member or Fetch Indirect.'
            ),
            array(
                'title'     => __LINE__.' name filter with member option',
                'options'   =>  array(
                                    P4_Group::FETCH_BY_NAME => "test",
                                    P4_Group::FETCH_BY_MEMBER => "user"
                                ),
                'exception' => 'Filter by Name is not compatible with Fetch by Member or Fetch Indirect.'
            ),
            array(
                'title'     => __LINE__.' name filter with indirect and member options',
                'options'   =>  array(
                                    P4_Group::FETCH_BY_NAME => "test",
                                    P4_Group::FETCH_BY_MEMBER => "user",
                                    P4_Group::FETCH_INDIRECT => true,
                                ),
                'exception' => 'Filter by Name is not compatible with Fetch by Member or Fetch Indirect.'
            ),
            array(
                'title'     => __LINE__.' empty string member filter',
                'options'   => array(P4_Group::FETCH_BY_MEMBER => ""),
                'exception' => 'Filter by Member expects a valid group or username.'
            ),
            array(
                'title'     => __LINE__.' integer member filter',
                'options'   => array(P4_Group::FETCH_BY_MEMBER => 10),
                'exception' => 'Filter by Member expects a valid group or username.'
            ),
            array(
                'title'     => __LINE__.' invalid member filter',
                'options'   => array(P4_Group::FETCH_BY_MEMBER => "-test"),
                'exception' => 'Filter by Member expects a valid group or username.'
            ),
        );

        foreach ($tests as $test) {
            try {
                P4_Group::fetchAll($test['options']);

                $this->fail($test['title'].': unexpected success');
            } catch (PHPUnit_Framework_AssertionFailedError $e) {
                $this->fail($e->getMessage());
            } catch (InvalidArgumentException $e) {
                $this->assertSame(
                    $test['exception'],
                    $e->getMessage(),
                    $test['title'].': unexpected exception message'
                );
            } catch (Exception $e) {
                $this->fail(
                    $test['title'].
                    ': unexpected exception ('. get_class($e) .') '.
                    $e->getMessage()
                );
            }
        }
    }

    /**
     * Test calling save without an ID
     */
    public function testSaveNoId()
    {
        try {
            $group = new P4_Group;
            $group->save();

            $this->fail('unexpected success');
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            $this->fail($e->getMessage());
        } catch (P4_Spec_Exception $e) {
            $this->assertSame(
                'Cannot save. Group is empty.',
                $e->getMessage(),
                'unexpected exception message'
            );
        } catch (Exception $e) {
            $this->fail(': unexpected exception ('. get_class($e) .') '. $e->getMessage());
        }
    }

    /**
     * Test fetch
     */
    public function testFetch()
    {
        // ensure fetch fails for a non-existant group.
        try {
            P4_Group::fetch('alskdfj2134');
            $this->fail("Fetch should fail for a non-existant group.");
        } catch (P4_Spec_NotFoundException $e) {
            $this->assertTrue(true);
        }

        // ensure fetch works for a just-created group
        $group = new P4_Group;
        $group->setId('testers%%')
              ->addUser('tester')
              ->save();

        $group = P4_Group::fetch('testers%%');
        $this->assertTrue($group->getId() == 'testers%%', "User id should be 'testers%%'.");
    }

    /**
     * Test id exists
     */
    public function testIdExists()
    {
        // ensure id-exists returns false for ill formatted group
        $this->assertFalse(P4_Group::exists("-alsdjf"), "Invalid group id should not exist.");

        // ensure id-exists returns false for non-existant group
        $this->assertFalse(P4_Group::exists("alsdjf"), "Given group id should not exist.");

        // create group and ensure it exists.
        $group = new P4_Group;
        $group->setId('test')
              ->addUser('tester')
              ->save();
        $this->assertTrue(P4_Group::exists("test"), 'Given group id should exist.');

        // check a group that contains position specifiers
        $group = new P4_Group;
        $group->setId('testers%%')
              ->addUser('tester')
              ->save();
        $this->assertTrue(P4_Group::exists('testers%%'), 'Given group id should exist.');
    }

    /**
     * test is empty
     */
    public function testIsEmpty()
    {
        $group = new P4_Group;
        $this->assertTrue(
            $group->isEmpty(),
            'Expected fresh group to be empty'
        );

        $group = new P4_Group;
        $this->assertTrue(
            $group->setId('test')->isEmpty(),
            'Expected group with ID to be empty'
        );

        $group = new P4_Group;
        $this->assertTrue(
            $group->setId('test%%')->isEmpty(),
            'Expected group with ID %% to be empty'
        );

        $group = new P4_Group;
        $this->assertTrue(
            $group->setTimeout(100)->isEmpty(),
            'Expected group with timeout to be empty'
        );

        $group = new P4_Group;
        $this->assertFalse(
            $group->addSubgroup('test')->isEmpty(),
            'Expected group with subgroup to not be empty'
        );

        $group = new P4_Group;
        $this->assertFalse(
            $group->addOwner('test')->isEmpty(),
            'Expected group with owner to not be empty'
        );

        $group = new P4_Group;
        $this->assertFalse(
            $group->addUser('test')->isEmpty(),
            'Expected group with user to not be empty'
        );
    }

    /**
     * test 'max' style fields with bad values
     */
    public function testBadMaxResultsMaxScanRowsMaxLockTimeTimeout()
    {
        $methods = array('MaxResults', 'MaxScanRows', 'MaxLockTime', 'Timeout');
        $tests = array(
            array(
                'title' => __LINE__.' bool',
                'value' => true,
                'error' => 'Type of input must be one of: null, int, string'
            ),
            array(
                'title' => __LINE__.' unsets',
                'value' => 'unsets',
                'error' => "For string input, only the values 'unlimited' and 'unset' are valid."
            ),
            array(
                'title' => __LINE__.' blank string',
                'value' => '',
                'error' => "For string input, only the values 'unlimited' and 'unset' are valid."
            ),
            array(
                'title' => __LINE__.' small negative number',
                'value' => -1,
                'error' => 'For integer input, only values greater than zero are valid.'
            ),
            array(
                'title' => __LINE__.' big negative number',
                'value' => -20000,
                'error' => 'For integer input, only values greater than zero are valid.'
            ),
            array(
                'title' => __LINE__.' zero',
                'value' => 0,
                'error' => 'For integer input, only values greater than zero are valid.'
            ),
        );

        foreach ($methods as $method) {
            foreach ($tests as $test) {
                $group = new P4_Group;

                try {
                    $group->{'set'.$method}($test['value']);

                    $this->fail($test['title'].', '. $method .': unexpected success');
                } catch (PHPUnit_Framework_AssertionFailedError $e) {
                    $this->fail($e->getMessage());
                } catch (InvalidArgumentException $e) {
                    $this->assertSame(
                        $test['error'],
                        $e->getMessage(),
                        $test['title'].', '. $method .': unexpected exception message'
                    );
                } catch (Exception $e) {
                    $this->fail(
                        $test['title'].', '. $method .
                        ': unexpected exception ('. get_class($e) .') '.
                        $e->getMessage()
                    );
                }
            }
        }
    }

    /**
     * test 'max' style fields with good values
     */
    public function testGoodMaxResultsMaxScanRowsMaxLockTimeTimeout()
    {
        $methods = array('MaxResults', 'MaxScanRows', 'MaxLockTime', 'Timeout');
        $tests = array(
            array(
                'title' => __LINE__.' small int',
                'value' => 1,
            ),
            array(
                'title' => __LINE__.' big int',
                'value' => 10000,
            ),
            array(
                'title' => __LINE__.' unset',
                'value' => 'unset',
                'out'   => null
            ),
            array(
                'title' => __LINE__.' null',
                'value' => null,
            ),
            array(
                'title' => __LINE__.' unlimited',
                'value' => 'unlimited',
            ),
        );

        foreach ($methods as $method) {
            foreach ($tests as $test) {
                $group = new P4_Group;

                $group->setId('test')->addUser('test');
                $group->{'set'.$method}($test['value']);

                $out = array_key_exists('out', $test) ? $test['out'] : $test['value'];

                // verify in-memory object matches up
                $this->assertSame(
                    $out,
                    $group->{'get'.$method}(),
                    $test['title'].', '. $method .': expected matching return'
                );

                // save and verify it still matches
                $group->save();
                $this->assertSame(
                    $out,
                    $group->{'get'.$method}(),
                    $test['title'].', '. $method .': expected matching return post save'
                );

                // fetch from storage and verify it still matches
                $group = P4_Group::fetch('test');
                $this->assertSame(
                    $out,
                    $group->{'get'.$method}(),
                    $test['title'].', '. $method .': expected matching return on fetched version'
                );

            }
        }
    }

    /**
     * Test behaviour when bad subgroups, owners, and users are specified.
     */
    public function testBadSubgroupsOwnersUsers()
    {
        $methods = array(
            'setSubgroups', 'addSubgroup',
            'setOwners',    'addOwner',
            'setUsers',     'addUser',
        );
        $arrayError   = '/^[^ ]+ must be specified as an array.$/';
        $elementError = '/^Individual [^ ]+ must be a valid ID in either string or [^ ]+ format.$/';
        $tests = array(
            array(
                'title' => __LINE__.' int',
                'value' => 10,
                'setError' => $arrayError,
                'addError' => $elementError
            ),
            array(
                'title' => __LINE__.' bool',
                'value' => true,
                'setError' => $arrayError,
                'addError' => $elementError
            ),
            array(
                'title' => __LINE__.' null',
                'value' => null,
                'setError' => $arrayError,
                'addError' => $elementError
            ),
            array(
                'title' => __LINE__.' array of ints',
                'value' => array(10, 9, 8),
                'setError' => $elementError,
                'addError' => $elementError
            ),
        );


        foreach ($methods as $method) {
            foreach ($tests as $test) {
                $group = new P4_Group;

                try {
                    $group->{$method}($test['value']);

                    $this->fail($test['title'].', '. $method .': unexpected success');
                } catch (PHPUnit_Framework_AssertionFailedError $e) {
                    $this->fail($e->getMessage());
                } catch (InvalidArgumentException $e) {
                    $error = $test[substr($method, 0, 3).'Error'];
                    $this->assertRegExp(
                        $error,
                        $e->getMessage(),
                        $test['title'].', '. $method .': unexpected exception message'
                    );
                } catch (Exception $e) {
                    $this->fail(
                        $test['title'].', '. $method .
                        ': unexpected exception ('. get_class($e) .') '.
                        $e->getMessage()
                    );
                }
            }
        }
    }

    /**
     * Test good set/add Subgroups, Owners, Users
     */
    public function testGoodSubgroupsOwnersUsers()
    {
        $group = new P4_Group;
        $group->setId('test');
        $user = new P4_User;
        $user->setId('test');

        $methods = array(
            'Subgroup' => $group,
            'Owner'    => $user,
            'User'     => $user,
        );

        try {
            foreach ($methods as $method => $object) {
                // test 'set'
                $group = new P4_Group;
                $group->setId('test');
                $group->{'set'.$method.'s'}(array($object));

                $this->assertSame(
                    (array)$object->getId(),
                    $group->{'get'.$method.'s'}(),
                    $method.': expected matching return following set'
                );

                // test 'add'
                $group->{'add'.$method}('test2');
                $this->assertSame(
                    array('test', 'test2'),
                    $group->{'get'.$method.'s'}(),
                    $method.': expected matching return following add'
                );

                // save and verify it still matches
                $group->save();
                $this->assertSame(
                    array('test', 'test2'),
                    $group->{'get'.$method.'s'}(),
                    $method.': expected matching return following save'
                );

                // fetch from storage and verify it still matches
                $group = P4_Group::fetch('test');
                $this->assertSame(
                    array('test', 'test2'),
                    $group->{'get'.$method.'s'}(),
                    $method.': expected matching return following fetch'
                );
            }
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            $this->fail($e->getMessage());
        } catch (Exception $e) {
            $this->fail(
                $method.': unexpected exception ('. get_class($e) .') '. $e->getMessage()
            );
        }
    }

    /**
     * Test save as owner.
     */
    public function testSaveAsOwner()
    {
        $user = new P4_User;
        $user->setId('owner-user')
             ->setFullName('Owner Of Group')
             ->setEmail('owner@domain.com')
             ->save();

        $group = new P4_Group;
        $group->setId('test-group')
              ->setUsers(array($user->getId()))
              ->setOwners(array($user->getId()))
              ->save();

        // connect as 'owner-user'.
        $connection = P4_Connection::factory(
            $this->p4->getPort(),
            $user->getId()
        );

        // save of group should fail w.out owner flag.
        try {
            $group = P4_Group::fetch('test-group', $connection);
            $group->save();
            $this->fail('unexpected success');
        } catch (Exception $e) {
            $this->assertTrue(true);
        }

        // now try to save the test-group w. owner flag.
        $group = P4_Group::fetch('test-group', $connection);
        $group->save(true);
    }
}

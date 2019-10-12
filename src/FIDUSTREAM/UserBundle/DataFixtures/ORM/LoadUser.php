<?php

namespace FIDUSTREAM\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use FIDUSTREAM\UserBundle\Entity\User;

class LoadUser implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        
        $viewer = new User();
        $contributor2 = new User();
        $contributor1 = new User();
        $moderator2 = new User();
        $moderator1 = new User();
        $admin = new User();


        $viewer->setUsername('viewer');
        $viewer->setPlainPassword('viewer');
        $viewer->setSalt('');
        $viewer->setEmail('viewer@viewer.com');
        $viewer->setAuthenticationLevel(5);
        $viewer->setRoles(array('ROLE_VIEWER'));
        $viewer->setEnabled(1);
        $manager->persist($viewer);
        

        $moderator1->setUsername('moderator1');
        $moderator1->setPlainPassword('moderator1');
        $moderator1->setSalt('');
        $moderator1->setEmail('mod1@mod1.com');
        $moderator1->setAuthenticationLevel(13);
        $moderator1->setRoles(array('ROLE_MODERATOR'));
        $moderator1->setEnabled(1);
        $manager->persist($moderator1);

        $moderator2->setUsername('moderator2');
        $moderator2->setPlainPassword('moderator2');
        $moderator2->setSalt('');
        $moderator2->setEmail('mod2@mod2.com');
        $moderator2->setAuthenticationLevel(9);
        $moderator2->setRoles(array('ROLE_MODERATOR'));
        $moderator2->setEnabled(1);
        $manager->persist($moderator2);

        $contributor1->setUsername('contributor1');
        $contributor1->setPlainPassword('contributor1');
        $contributor1->setSalt('');
        $contributor1->setEmail('cont1@cont1.com');
        $contributor1->setModerator($moderator1);
        $contributor1->setAuthenticationLevel(3);
        $contributor1->setRoles(array('ROLE_CONTRIBUTOR'));
        $contributor1->setEnabled(1);
        $manager->persist($contributor1);

        $contributor2->setUsername('contributor2');
        $contributor2->setPlainPassword('contributor2');
        $contributor2->setSalt('');
        $contributor2->setEmail('cont2@cont2.com');
        $contributor2->setAuthenticationLevel(15);
        $contributor2->setModerator($moderator2);
        $contributor2->setRoles(array('ROLE_CONTRIBUTOR'));
        $contributor2->setEnabled(1);
        $manager->persist($contributor2);

        $admin->setUsername('admin');
        $admin->setPlainPassword('admin');
        $admin->setSalt('');
        $admin->setEmail('admin@admin.com');
        $admin->setAuthenticationLevel(1);
        $admin->setModerator($admin);
        $admin->setRoles(array('ROLE_ADMIN'));
        $admin->setEnabled(1);
        $manager->persist($admin);
        

        $manager->flush();
    }
}
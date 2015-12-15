<?php
namespace LoginCidadao\OAuthBundle\Entity;

use FOS\OAuthServerBundle\Entity\ClientManager as FOSClientManager;
use FOS\OAuthServerBundle\Model\ClientInterface;
use Doctrine\ORM\PersistentCollection;
use LoginCidadao\CoreBundle\Entity\Person;

class ClientManager extends FOSClientManager
{

    public function updateClient(ClientInterface $client)
    {
        // let's see what happens
        $this->em->persist($client);
        $this->em->flush();
        return;

        $this->em->getConnection()->beginTransaction();
        if ($client->getId()) {
            $this->em->createQuery('DELETE from LoginCidadaoOAuthBundle:ClientPerson where client_id = :id')->setParameter('id', $client->getId());
        }
        $this->em->persist($client);
        $itens = $client->getPersons();
        if ($itens instanceof PersistentCollection) {
            foreach ($itens as $idx => $iten) {
                if ($iten instanceof Person) {
                    $itens->removeElement($iten);
                    $new = new ClientPerson();
                    $new->setClient($client);
                    $new->setPerson($iten);
                    $this->em->persist($new);
                    $itens->add($new);
                }
            }
        } else if (is_array($itens)) {
            foreach ($itens as $idx => $iten) {
                if ($iten instanceof Person) {
                    $new = new ClientPerson();
                    $new->setClient($client);
                    $new->setPerson($iten);
                    $this->em->persist($new);
                } else if ($iten instanceof ClientPerson) {
                    $iten->setClient($client);
                    $this->em->persist($iten);
                }
            }
        }
        $this->em->flush();
        $this->em->getConnection()->commit();
    }
}
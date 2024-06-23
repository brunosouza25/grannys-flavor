<?php

namespace App\Repository;

use App\Entity\EmailsToSend;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmailsToSend>
 *
 * @method EmailsToSend|null find($id, $lockMode = null, $lockVersion = null)
 * @method EmailsToSend|null findOneBy(array $criteria, array $orderBy = null)
 * @method EmailsToSend[]    findAll()
 * @method EmailsToSend[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmailsToSendRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmailsToSend::class);
    }

    public function add(EmailsToSend $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(EmailsToSend $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    public function newEmailToSend($body, $subject, $destinationEmail, $destinationName)
    {
        $newEmailToSend = new EmailsToSend();
        $newEmailToSend->setBody($body);
        $newEmailToSend->setSubject($subject);
        $newEmailToSend->setDestinationEmail($destinationEmail);
        $newEmailToSend->setDestinationName($destinationName);
        $newEmailToSend->setStatus(0);

        $this->getEntityManager()->persist($newEmailToSend);
        $this->getEntityManager()->flush();
    }
    public function deleteEmail($id)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->delete(EmailsToSend::class, 'e')
            ->where('e.id = :id')
            ->setParameter('id', $id);

        return $queryBuilder->getQuery()->execute();
    }

    public function updateEmailStatus($id)
    {
        $queryBuilder = $this->createQueryBuilder('e');
        $query = $queryBuilder
            ->update('App\Entity\EmailsToSend', 'e')
            ->set('e.status', ':status')
            ->where('e.id = :id')
            ->setParameter('status', 1)
            ->setParameter('id', $id)
            ->getQuery();

        $query->execute();
    }
//    /**
//     * @return EmailsToSend[] Returns an array of EmailsToSend objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?EmailsToSend
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

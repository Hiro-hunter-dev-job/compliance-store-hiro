<?php

namespace App\Application\UseCase\CreateHolder;


use App\Domain\Entity\Holder;
use App\Domain\Exception\HolderAlreadyExistsException;
use App\Domain\Port\Repository\HolderRepositoryInterface;
use App\Domain\Port\Service\GeneratorInterface;
use App\Domain\ValueObject\BirthDate;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\HolderId;
use App\Domain\ValueObject\HolderName;

final readonly class CreateHolderUseCase
{
    public function __construct(
        private GeneratorInterface $generatorUuid,
        private HolderRepositoryInterface $holderRepository
    ){}

    public function execute(CreateHolderCommand $command) : void
    {
        $value = $this->generatorUuid->generate();
        $holderId = HolderId::fromString($value);
        $birthDate = BirthDate::fromString($command->birthDate);
        $holderName = new HolderName($command->holderName);
        $email = new Email($command->email);

        if (null !== $this->holderRepository->findByEmail($email->value)) {
            throw new HolderAlreadyExistsException($holderId);
        }

        $holder = Holder::register(
            $holderId,
            $holderName,
            $birthDate,
            $email
        );

        $this->holderRepository->add($holder);
    }
}

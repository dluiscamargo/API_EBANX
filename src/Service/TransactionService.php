<?php

namespace App\Service;

use App\Repository\AccountRepository;

class TransactionService
{
    private AccountRepository $repository;

    public function __construct(AccountRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getBalance(string $accountId): ?int
    {
        $account = $this->repository->findById($accountId);
        return $account ? (int)$account['balance'] : null;
    }

    public function deposit(string $accountId, int $amount): array
    {
        $account = $this->repository->findById($accountId);
        $currentBalance = $account ? (int)$account['balance'] : 0;
        $newBalance = $currentBalance + $amount;

        $this->repository->createOrUpdate($accountId, $newBalance);

        return ['id' => $accountId, 'balance' => $newBalance];
    }

    public function withdraw(string $accountId, int $amount): ?array
    {
        $account = $this->repository->findById($accountId);
        if (!$account) {
            return null; // Account not found
        }

        $currentBalance = (int)$account['balance'];
        
        // The original logic didn't seem to check for sufficient funds here, 
        // but it's good practice. The controller can handle the response.
        // For now, let's stick to the original flow where this might not have been checked.
        $newBalance = $currentBalance - $amount;
        $this->repository->createOrUpdate($accountId, $newBalance);
        
        return ['id' => $accountId, 'balance' => $newBalance];
    }

    public function transfer(string $fromId, string $toId, int $amount): ?array
    {
        $originAccount = $this->repository->findById($fromId);
        if (!$originAccount) {
            return null; // Origin account not found
        }
        
        // Withdraw from origin
        $originBalance = (int)$originAccount['balance'];
        $newOriginBalance = $originBalance - $amount;
        $this->repository->createOrUpdate($fromId, $newOriginBalance);

        // Deposit into destination
        $destinationAccount = $this->repository->findById($toId);
        $destinationBalance = $destinationAccount ? (int)$destinationAccount['balance'] : 0;
        $newDestinationBalance = $destinationBalance + $amount;
        $this->repository->createOrUpdate($toId, $newDestinationBalance);

        return [
            'origin' => ['id' => $fromId, 'balance' => $newOriginBalance],
            'destination' => ['id' => $toId, 'balance' => $newDestinationBalance]
        ];
    }
}

<?php

declare(strict_types=1);


namespace App\Controllers;

use App\Contracts\RequestValidatorFactoryInterface;
use App\DataObjects\TransactionData;
use App\RequestValidators\TransactionImportRequestValidator;
use App\Services\CategoryService;
use App\Services\TransactionService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class TransactionImporterController
{
    public function __construct(
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly TransactionService $transactionService,
        private readonly CategoryService $categoryService,
    )
    {
    }

    public function import(Response $response, Request $request): Response
    {
        $file = $this->requestValidatorFactory->make(TransactionImportRequestValidator::class)->validate(
            $request->getUploadedFiles()
        )['importFIle'];

        $user = $request->getAttribute('user');
        $resource = fopen($file->getStream()->getMetaData('uri'), 'r');

        fgetcsv($resource);

        while (($row = fgetcsv($resource)) !== false) {
            [$date, $description, $category, $amount] = $row;

            $date = new \DateTime($date);
            $category = $this->categoryService->findByName($category);
            $amount = str_replace(['$',','], '', $amount);

            $transactionData = new TransactionData($description, (float) $amount, $date, $category);

            $this->transactionService->create($transactionData, $user);
        }

        return $response;
    }
}
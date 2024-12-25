<?php

declare(strict_types = 1);

namespace App\Controllers;

use App\Contracts\RequestValidatorFactoryInterface;
use App\ResponseFormatter;
use App\Services\CategoryService;
use App\Services\TransactionService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\SimpleCache\InvalidArgumentException;
use Slim\Views\Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class HomeController
{
    public function __construct(
        private readonly Twig $twig,
        private readonly TransactionService $transactionService,
        private readonly CategoryService $categoryService,
        private readonly ResponseFormatter $responseFormatter,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory
    ) {
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws InvalidArgumentException
     * @throws LoaderError
     */
    public function index(Response $response, Request $request): Response
    {
        $startDate             = \DateTime::createFromFormat('Y-m-d', date('2023-01-01'));
        $endDate               = new \DateTime('now');
        $userId                = $request->getAttribute('user')->getId();
        $totals                = $this->transactionService->getTotals($startDate, $endDate, $userId);
        $recentTransactions    = $this->transactionService->getRecentTransactions(10, $userId);
        $topSpendingCategories = $this->categoryService->getTopSpendingCategories(4, $userId);

        return $this->twig->render(
            $response,
            'dashboard.twig',
            [
                'totals'                => $totals,
                'transactions'          => $recentTransactions,
                'topSpendingCategories' => $topSpendingCategories,
            ]
        );
    }

    public function getYearToDateStatistics(Response $response, Request $request): Response
    {

        $data = $this->transactionService->getMonthlySummary((int) date('2023-01-01'), $request->getAttribute('user')->getId());

        return $this->responseFormatter->asJson($response, $data);
    }

    public function getCustomStatistics(Response $response, Request $request): Response
    {
        //TODO
        $startDate             = \DateTime::createFromFormat('Y-m-d' ,$request->getParsedBody()['start-date']);
        $endDate               = \DateTime::createFromFormat('Y-m-d' ,$request->getParsedBody()['end-date']);
        $userId                = $request->getAttribute('user')->getId();


            $totals                = $this->transactionService->getTotals($startDate, $endDate, $userId);

        var_dump($totals);

        return $this->twig->render(
            $response,
            'dashboard.twig',
            [
                'totals' => $totals
            ]
        );
    }

}

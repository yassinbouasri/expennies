<?php

declare(strict_types=1);

namespace App\Controllers;

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
use DateTime;

class HomeController
{
    public int $year = 2024;

    public function __construct(
        private readonly Twig $twig,
        private readonly TransactionService $transactionService,
        private readonly CategoryService $categoryService,
        private readonly ResponseFormatter $responseFormatter,
    )
    {
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws InvalidArgumentException
     * @throws LoaderError
     */
    public function index(Response $response, Request $request): Response
    {
        $defaultStartDate = DateTime::createFromFormat('Y-m-d', date('2024-12-01'));
        $defaultEndDate = new DateTime('now');

        $startDate = DateTime::createFromFormat('Y-m-d', $request->getParsedBody()['start-date'] ?? $defaultStartDate->format('Y-m-d'));
        $endDate = DateTime::createFromFormat('Y-m-d', $request->getParsedBody()['end-date'] ?? $defaultEndDate->format('Y-m-d'));

        $year = date("Y", strtotime($startDate->format('Y-m-d')));
        $this->getYear((int)$year);
        setcookie('year', $year, time() + 3600, '/');

        $userId = $request->getAttribute('user')->getId();
        $totals = $this->transactionService->getTotals($startDate, $endDate, $userId);

        $recentTransactions = $this->transactionService->getRecentTransactions(10, $userId);
        $topSpendingCategories = $this->categoryService->getTopSpendingCategories(4, $userId);

        return $this->twig->render($response, 'dashboard.twig', ['totals' => $totals, 'transactions' => $recentTransactions, 'topSpendingCategories' => $topSpendingCategories, 'startDate' => $startDate->format('Y-m-d'), 'endDate' => $endDate->format('Y-m-d'), 'year' => $year,]);
    }

    public function getYearToDateStatistics(Response $response, Request $request): Response
    {
        $year = isset($_COOKIE['year']) ? (int)$_COOKIE['year'] : (int)date('Y');

        $data = $this->transactionService->getMonthlySummary($year, $request->getAttribute('user')->getId());

        return $this->responseFormatter->asJson($response, $data);
    }

    public function getYear(int $year): int
    {
        $this->year = $year;

        return $year;
    }

}

<?phpdeclare(strict_types=1);namespace App\Service\SearchEngine;interface SearchModelInterface{    public function getOffset(): int;    public function getLimit(): int;}
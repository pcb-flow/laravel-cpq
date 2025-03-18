<?php

namespace PcbFlow\CPQ\Services;

use Illuminate\Support\Collection;
use PcbFlow\CPQ\DTOs\CostQuoteDTO;
use PcbFlow\CPQ\DTOs\LeadtimeQuoteDTO;
use PcbFlow\CPQ\DTOs\ProductQuoteDTO;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class QuoteService
{
    /**
     * @param \PcbFlow\CPQ\Models\Product $product
     * @param array $params
     * @return \PcbFlow\CPQ\DTOs\ProductQuoteDTO
     */
    public function quoteProduct($product, $params)
    {
        $costQuoteDTOs = $this->quoteCosts($product, $params);

        $leadtimeQuoteDTO = $this->quoteLeadtime($product, $params);

        return new ProductQuoteDTO($costQuoteDTOs, $leadtimeQuoteDTO);
    }

    /**
     * @param \PcbFlow\CPQ\Models\Product $product
     * @param array $params
     * @return \Illuminate\Support\Collection
     */
    public function quoteCosts($product, $params)
    {
        $costQuoteDTOs = new Collection();

        foreach ($product->costs as $cost) {
            $quoteCostDto = $this->quoteCost($cost, $params);

            if (!is_null($quoteCostDto)) {
                $costQuoteDTOs->push($quoteCostDto);
            }
        }

        return $costQuoteDTOs;
    }

    /**
     * @param \PcbFlow\CPQ\Models\Cost $cost
     * @param array $params
     * @return \PcbFlow\CPQ\DTOs\CostQuoteDTO|null
     */
    public function quoteCost($cost, $params)
    {
        $expression = new ExpressionLanguage();

        foreach ($cost->rules as $rule) {
            if (empty($rule->condition) || (bool) $expression->evaluate($rule->condition, $params)) {
                $price = (float) $expression->evaluate($rule->action, $params);

                return new CostQuoteDTO($price, $cost, $rule);
            }
        }

        return null;
    }

    /**
     * @param \PcbFlow\CPQ\Models\Product $product
     * @param array $params
     * @return \PcbFlow\CPQ\DTOs\LeadtimeQuoteDTO|null
     */
    public function quoteLeadtime($product, $params)
    {
        $expression = new ExpressionLanguage();

        foreach ($product->leadtimes as $leadtime) {
            if (empty($leadtime->condition) || (bool) $expression->evaluate($leadtime->condition, $params)) {
                return new LeadtimeQuoteDTO($leadtime);
            }
        }

        return null;
    }
}

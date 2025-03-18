<?php

namespace PcbFlow\CPQ\DTOs;

use Illuminate\Contracts\Support\Arrayable;

class ProductQuoteDTO implements Arrayable
{
    /**
     * @var \Illuminate\Support\Collection<\PcbFlow\CPQ\DTOs\CostQuoteDTO>
     */
    public $costQuoteDTOs;

    /**
     * @var \PcbFlow\CPQ\DTOs\LeadtimeQuoteDTO|null
     */
    public $leadtimeQuoteDTO;

    /**
     * @param \Illuminate\Support\Collection $costQuoteDTOs
     * @param \PcbFlow\CPQ\DTOs\LeadtimeQuoteDTO|null $leadtimeQuoteDTO
     */
    public function __construct($costQuoteDTOs, $leadtimeQuoteDTO = null)
    {
        $this->costQuoteDTOs = $costQuoteDTOs;

        $this->leadtimeQuoteDTO = $leadtimeQuoteDTO;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'costs' => $this->costQuoteDTOs->toArray(),
            'leadtime' => !is_null($this->leadtimeQuoteDTO) ? $this->leadtimeQuoteDTO->toArray() : null,
        ];
    }
}

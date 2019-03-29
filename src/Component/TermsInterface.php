<?php

namespace Solarium\Component;

use Solarium\Core\ConfigurableInterface;

/**
 * Terms interface.
 */
interface TermsInterface extends ConfigurableInterface
{
    /**
     * Set the field name(s) to get the terms from.
     *
     * For multiple fields use a comma-separated string or array
     *
     * @param string|array $value
     *
     * @return self Provides fluent interface
     */
    public function setFields($value): TermsInterface;

    /**
     * Get the field name(s) to get the terms from.
     *
     * @return array
     */
    public function getFields(): array;

    /**
     * Set the lowerbound term to start at.
     *
     * @param string $value
     *
     * @return self Provides fluent interface
     */
    public function setLowerbound(string $value): TermsInterface;

    /**
     * Get the lowerbound term to start at.
     *
     * @return string|null
     */
    public function getLowerbound(): ?string;

    /**
     * Set lowerboundinclude.
     *
     * @param bool $value
     *
     * @return self Provides fluent interface
     */
    public function setLowerboundInclude(bool $value): TermsInterface;

    /**
     * Get lowerboundinclude.
     *
     * @return bool|null
     */
    public function getLowerboundInclude(): ?bool;

    /**
     * Set mincount (the minimum doc frequency for terms in order to be included).
     *
     * @param int $value
     *
     * @return self Provides fluent interface
     */
    public function setMinCount(int $value): TermsInterface;

    /**
     * Get mincount.
     *
     * @return int|null
     */
    public function getMinCount(): ?int;

    /**
     * Set maxcount (the maximum doc frequency for terms in order to be included).
     *
     * @param int $value
     *
     * @return self Provides fluent interface
     */
    public function setMaxCount(int $value): TermsInterface;

    /**
     * Get maxcount.
     *
     * @return int|null
     */
    public function getMaxCount(): ?int;

    /**
     * Set prefix for terms.
     *
     * @param string $value
     *
     * @return self Provides fluent interface
     */
    public function setPrefix(string $value): TermsInterface;

    /**
     * Get maxcount.
     *
     * @return string|null
     */
    public function getPrefix(): ?string;

    /**
     * Set regex to restrict terms.
     *
     * @param string $value
     *
     * @return self Provides fluent interface
     */
    public function setRegex(string $value): TermsInterface;

    /**
     * Get regex.
     *
     * @return string
     */
    public function getRegex(): ?string;

    /**
     * Set regex flags.
     *
     * Use a comma-separated string or array for multiple entries
     *
     * @param string|array $value
     *
     * @return self Provides fluent interface
     */
    public function setRegexFlags($value): TermsInterface;

    /**
     * Get regex flags.
     *
     * @return array|null
     */
    public function getRegexFlags(): ?array;

    /**
     * Set limit.
     *
     * If < 0 all terms are included
     *
     * @param int $value
     *
     * @return self Provides fluent interface
     */
    public function setLimit(int $value): TermsInterface;

    /**
     * Get limit.
     *
     * @return int|null
     */
    public function getLimit(): ?int;

    /**
     * Set the upperbound term to start at.
     *
     * @param string $value
     *
     * @return self Provides fluent interface
     */
    public function setUpperbound(string $value): TermsInterface;

    /**
     * Get the upperbound term to start at.
     *
     * @return string|null
     */
    public function getUpperbound(): ?string;

    /**
     * Set upperboundinclude.
     *
     * @param bool $value
     *
     * @return self Provides fluent interface
     */
    public function setUpperboundInclude(bool $value): TermsInterface;

    /**
     * Get upperboundinclude.
     *
     * @return bool|null
     */
    public function getUpperboundInclude(): ?bool;

    /**
     * Set raw option.
     *
     * @param bool $value
     *
     * @return self Provides fluent interface
     */
    public function setRaw(bool $value): TermsInterface;

    /**
     * Get raw option.
     *
     * @return bool
     */
    public function getRaw(): ?bool;

    /**
     * Set sort option.
     *
     * @param string $value
     *
     * @return self Provides fluent interface
     */
    public function setSort(string $value): TermsInterface;

    /**
     * Get sort option.
     *
     * @return string|null
     */
    public function getSort(): ?string;
}

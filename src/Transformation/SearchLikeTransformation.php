<?php

namespace Drupal\field_encrypt_searchable\Transformation;

use ParagonIE\ConstantTime\Binary;
use ParagonIE\CipherSweet\Contract\TransformationInterface;

/**
 * Class SearchLikeTransformation.
 * @package ParagonIE\CipherSweet\Transformation
 */
class SearchLikeTransformation implements TransformationInterface
{

  private $position;

  private $length;

  public function __construct($position, $length)
  {
    $this->position = $position;
    $this->length = $length;
  }


  /**
   * Returns the last 4 digits (e.g. for a social security or credit card
   * number). If less then 4 digits are available, it will pad them with 0
   * characters to the left.
   *
   * 1234567890 => 7890
   * 123        => 0123
   *
   * @param string $input
   * @return string
   */
  public function __invoke($input)
  {
    return Binary::safeSubstr($input, $this->position, $this->length);
  }
}

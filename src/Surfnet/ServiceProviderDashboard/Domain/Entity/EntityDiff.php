<?php



/**
 * Copyright 2022 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Surfnet\ServiceProviderDashboard\Domain\Entity;

class EntityDiff
{
    private readonly array $diff;

    public function __construct(array $data, array $compareTo)
    {
        $this->diff = $this->arrayRecursiveDiff($data, $compareTo);
    }

    public function getDiff(): array
    {
        return $this->diff;
    }

    /**
     * Recursive diff algorithm.
     * Kindly shared by mhitza
     * See https://stackoverflow.com/questions/3876435/recursive-array-diff
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function arrayRecursiveDiff(array $data, array $originalData): array
    {
        $diffResults = [];
        foreach ($data as $key => $value) {
            if (array_key_exists($key, $originalData)) {
                if (is_array($value)) {
                    $recursiveDiff = $this->arrayRecursiveDiff($value, $originalData[$key]);
                    // Redirect urls should not be diffed, the complete list should be provided
                    // Grants should not be diffed, the complete list should be provided
                    // But only when the values changed between the two different versions
                    if ($recursiveDiff !== []
                        && ($key === 'metaDataFields.redirectUrls' || $key === 'metaDataFields.grants')
                    ) {
                        $recursiveDiff = $value;
                    }
                    if ($recursiveDiff !== []) {
                        $diffResults[$key] = $recursiveDiff;
                    }
                } elseif ($value != $originalData[$key]) {
                    // When a secret is not changed (resulting in an empty value) do not include it in the diff.
                    if ($key === 'metaDataFields.secret' && $value === '') {
                        continue;
                    }
                    $diffResults[$key] = $value;
                }
            } else {
                $diffResults[$key] = $value;
            }
        }

        // Before returning the diff. Test if the source array contains keys not present in the new data. That way
        // we can also mark removed metadata items correctly.
        foreach (array_keys($originalData) as $key) {
            if (!array_key_exists($key, $data)) {
                $diffResults[$key] = null;
            }
        }

        return $diffResults;
    }
}

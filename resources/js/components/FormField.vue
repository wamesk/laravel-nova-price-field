<template>
  <DefaultField
    :field="currentField"
    :errors="errors"
    :show-help-text="showHelpText"
    :full-width-content="fullWidthContent"
  >
    <template #field>
      <input
        :id="currentField.attribute"
        type="text"
        class="w-full form-control form-input form-control-bordered"
        :class="errorClasses"
        :placeholder="currentField.name"
        v-model="value"
        v-if="!field.with_all_field_on_form"
        @change="emitChange"
      />
      <div v-if="field.with_all_field_on_form">
        <label
            class="inline-block leading-tight space-x-1 mb-1 alternative-component-form-label"
            :for="currentField.attribute">
            {{__('price_with_tax')}}
        </label>
        <input
            :id="currentField.attribute"
            :name="currentField.attribute"
            type="text"
            class="w-full form-control form-input form-control-bordered"
            :class="errorClasses"
            :placeholder="__('price_with_tax')"
            v-model="value"
            @change="calculatePriceWithoutTax"
        />

        <label
            v-if="!currentField.hide_tax_form_field && currentField.vat_rate_types === undefined"
            class="inline-block leading-tight space-x-1 mt-2 mb-1 alternative-component-form-label"
            :for="currentField.attribute+'_tax_percentage'"
        >
          {{__('tax_percentage')}}
        </label>
        <input
            :id="currentField.attribute+'_tax_percentage'"
            :name="currentField.attribute+'_tax_percentage'"
            type="text"
            class="w-full form-control form-input form-control-bordered"
            :class="errorClasses"
            :placeholder="__('tax_percentage')"
            v-model="valueTax"
            :hidden="currentField.hide_tax_form_field"
            v-if="currentField.vat_rate_types === undefined"
            @change="calculatePriceWithTax"
        />

        <label
            v-if="!currentField.hide_tax_form_field && currentField.vat_rate_types !== undefined"
            class="inline-block leading-tight space-x-1 mt-2 mb-1 alternative-component-form-label"
            :for="currentField.attribute+'_tax_percentage'"
        >
          {{__('tax_rate_type')}}
        </label>
        <select
            :id="currentField.attribute+'_tax_percentage'"
            :name="currentField.attribute+'_tax_percentage'"
            class="w-full block form-control form-control-bordered form-input"
            :class="errorClasses"
            v-model="valueTax"
            v-if="currentField.vat_rate_types !== undefined"
            @change="calculatePriceWithTax"
        >
            <option selected="" disabled="" value="">Vyberte možnosť</option>
            <option
                v-for="(vatRateTypeOption, value) in currentField.vat_rate_types"
                :value="value"
            >
              {{ vatRateTypeOption.name }}
            </option>
        </select>

        <label
            class="inline-block leading-tight space-x-1 mt-2 mb-1 alternative-component-form-label"
            :for="currentField.attribute+'_without_tax'">
            {{__('price_without_tax')}}
        </label>
        <input
            :id="currentField.attribute+'_without_tax'"
            :name="currentField.attribute+'_without_tax'"
            type="text"
            class="w-full form-control form-input form-control-bordered"
            :class="errorClasses"
            :placeholder="__('price_without_tax')"
            v-model="valueWithoutTax"
            @change="calculatePriceWithTax"
        />
      </div>
    </template>
  </DefaultField>
</template>

<script>
import { DependentFormField, HandlesValidationErrors } from 'laravel-nova'
import {isString, round} from "lodash";

export default {
  mixins: [DependentFormField, HandlesValidationErrors],

  props: ['resourceName', 'resourceId', 'field'],

  data() {
    return {
      valueWithoutTax: null,
      valueTax: null,
    }
  },

  watch: {
    // whenever question changes, this function will run
    currentField(newCurrentField, oldCurrentField) {
      if (newCurrentField.taxValue !== oldCurrentField.taxValue) {
        this.valueTax = newCurrentField.taxValue;
        this.calculatePriceWithoutTax();
      }
    },
  },

  methods: {
    /*
     * Set the initial, internal value for the field.
     */
    setInitialValue() {
      this.value = this.currentField.value ?? '';
      this.valueTax = this.currentField.taxValue ?? '';
      this.calculatePriceWithoutTax();
    },

    /**
     * Fill the given FormData object with the field's internal value.
     */
    fill(formData) {
      formData.append(this.fieldAttribute, this.emptyToNull(this.parseNumber(this.value)))
      formData.append(this.fieldAttribute+'_without_tax', this.emptyToNull(this.parseNumber(this.valueWithoutTax)))
      formData.append(this.fieldAttribute+'_tax', this.emptyToNull(this.parseNumber(this.valueTax)))
      formData.append(this.fieldAttribute+'_vat_rate_type', this.currentField.vat_rate_types?.[this.valueTax]?.id ?? null)
    },

    calculatePriceWithoutTax() {
      if (this.value === null || this.value === '') {
        this.valueWithoutTax = null;
        return;
      }

      this.value = round(this.parseNumber(this.value), 2);

      this.valueWithoutTax = round(this.value / this.getTaxCalculator(), 2);
    },

    calculatePriceWithTax() {
      if (this.valueWithoutTax === null || this.valueWithoutTax === '') {
        this.value = null;
        return;
      }

      this.valueWithoutTax = round(this.parseNumber(this.valueWithoutTax), 2);

      this.value = round(this.valueWithoutTax * this.getTaxCalculator(), 2);
    },

    getTaxCalculator() {
      return (this.parseNumber(this.valueTax) / 100) + 1;
    },

    /**
     * Normalize a decimal string that uses a comma as the decimal separator
     * (e.g. "10,20" typed on keyboards with a numeric comma) into a dot-based
     * value, so lodash round()/toNumber() does not turn it into NaN.
     */
    parseNumber(value) {
      if (typeof value === 'string') {
        return value.replace(',', '.');
      }

      return value;
    },

    /**
     * Convert only genuinely empty input to null, preserving a legitimate 0.
     * A plain `value || null` would send 0 as null and break NOT NULL columns.
     */
    emptyToNull(value) {
      return (value === null || value === undefined || value === '') ? null : value;
    },
  },
}
</script>

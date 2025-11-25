<script>
export default {
    props: [
        'field',
    ],
    name: "Price"
}
</script>

<template>
    <div v-if="!field.show_total">
        <slot name="value">
            <p
                v-if="field.formatted_price_with_tax && !field.only_without_tax && !shouldDisplayAsHtml"
                class="flex items-center"
            >
                {{ __('with_tax', {price: field.formatted_price_with_tax}) }}
            </p>
            <div
                v-else-if="field.value && shouldDisplayAsHtml"
                v-html="field.value"
            />
            <p v-else-if="!field.only_without_tax">&mdash;</p>
        </slot>
        <slot name="value">
            <p
                v-if="field.formatted_price_without_tax && !field.only_with_tax && !shouldDisplayAsHtml"
                class="flex items-center"
            >
                {{ __('without_tax', {price: field.formatted_price_without_tax}) }}
            </p>
        </slot>
        <slot name="value">
            <p
                v-if="field.formatted_tax_amount && !field.only_with_tax && !field.without_tax_amount && !shouldDisplayAsHtml"
                class="flex items-center"
            >
                {{ __('tax_amount', {price: field.formatted_tax_amount}) }} <span v-if="!field.hide_tax_percentage">({{ field.formatted_tax_percentage }})</span>
            </p>
        </slot>
    </div>
    <div v-if="field.show_total">
        <slot name="value">
            <p
                v-if="field.formatted_total_price_with_tax && !field.only_without_tax && !shouldDisplayAsHtml"
                class="flex items-center"
            >
                {{ __('with_tax', {price: field.formatted_total_price_with_tax}) }}
            </p>
            <div
                v-else-if="field.value && shouldDisplayAsHtml"
                v-html="field.value"
            />
            <p v-else-if="!field.only_without_tax">&mdash;</p>
        </slot>
        <slot name="value">
            <p
                v-if="field.formatted_total_price_without_tax && !field.only_with_tax && !shouldDisplayAsHtml"
                class="flex items-center"
            >
                {{ __('without_tax', {price: field.formatted_total_price_without_tax}) }}
            </p>
        </slot>
        <slot name="value">
            <p
                v-if="field.formatted_total_tax_amount && !field.only_with_tax && !field.without_tax_amount && !shouldDisplayAsHtml"
                class="flex items-center"
            >
                {{ __('tax_amount', {price: field.formatted_total_tax_amount}) }} <span v-if="!field.hide_tax_percentage">({{ field.formatted_tax_percentage }})</span>
            </p>
        </slot>
    </div>
</template>

<style scoped>

</style>

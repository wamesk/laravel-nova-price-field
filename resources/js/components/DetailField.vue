<template>
    <div
        class="flex flex-col -mx-6 px-6 py-2 space-y-2"
        :class="[
      'md:flex-row @sm/peekable:flex-row @md/modal:flex-row',
      'md:py-0 @sm/peekable:py-0 @md/modal:py-0',
      'md:space-y-0 @sm/peekable:space-y-0 @md/modal:space-y-0',
    ]"
        :dusk="field.attribute"
    >
        <div
            :class="[
        'md:w-1/4 @sm/peekable:w-1/4 @md/modal:w-1/4',
        'md:py-3 @sm/peekable:py-3 @md/modal:py-3',
      ]"
        >
            <slot>
                <h4 class="font-normal @sm/peekable:break-all">
                    <span>{{ field.name }}</span>
                </h4>
            </slot>
        </div>
        <div
            class="break-all"
            :class="[
        'md:w-3/4 @sm/peekable:w-3/4 @md/modal:w-3/4',
        'md:py-3 @sm/peekable:py-3 md/modal:py-3',
        'lg:break-words @md/peekable:break-words @lg/modal:break-words',
      ]"
        >
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
                        v-if="field.formatted_tax_amount && !field.without_tax_amount && !shouldDisplayAsHtml"
                        class="flex items-center"
                    >
                        {{ __('tax_amount', {price: field.formatted_tax_amount}) }}
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
                        v-if="field.formatted_total_tax_amount && !field.without_tax_amount && !shouldDisplayAsHtml"
                        class="flex items-center"
                    >
                        {{ __('tax_amount', {price: field.formatted_total_tax_amount}) }}
                    </p>
                </slot>
            </div>
        </div>
    </div>
</template>

<script>
export default {
  props: ['index', 'resource', 'resourceName', 'resourceId', 'field'],
}
</script>

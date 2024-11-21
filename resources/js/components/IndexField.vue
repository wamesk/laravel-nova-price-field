<template>
    <Price
        :field="field"
    />
</template>

<script>
import Price from './Price.vue';

export default {
    props: ['resourceName', 'field'],

    components: {
        Price,
    },

    computed: {
        fieldValue() {
            return this.field.displayedAs || this.field.value
        },
    }
}
</script>

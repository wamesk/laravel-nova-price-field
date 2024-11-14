import IndexField from './components/IndexField.vue'
import DetailField from './components/DetailField.vue'
import FormField from './components/FormField.vue'

Nova.booting((app, store) => {
  app.component('index-laravel-nova-price-field', IndexField)
  app.component('detail-laravel-nova-price-field', DetailField)
  app.component('form-laravel-nova-price-field', FormField)
})

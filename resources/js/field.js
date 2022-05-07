import FormField from './components/FormField'

Nova.booting((app, store) => {
  app.component('form-ajax-select', FormField)
})

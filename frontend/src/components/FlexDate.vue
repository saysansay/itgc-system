<template>
  <flat-pickr
    class="form-control"
    :class="{ 'is-invalid': invalid }"
    :model-value="modelValue"
    :config="resolvedConfig"
    @update:modelValue="$emit('update:modelValue', $event)"
  />
</template>

<script setup>
import { computed } from 'vue'
import FlatPickr from 'vue-flatpickr-component'

defineOptions({ name: 'FlexDate' })

const props = defineProps({
  modelValue: {
    type: [String, Number, Date],
    default: ''
  },
  config: {
    type: Object,
    default: () => ({})
  },
  invalid: {
    type: Boolean,
    default: false
  }
})

defineEmits(['update:modelValue'])

const resolvedConfig = computed(() => ({
  dateFormat: 'Y-m-d',
  allowInput: true,
  time_24hr: true,
  ...props.config
}))
</script>

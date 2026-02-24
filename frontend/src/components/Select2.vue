<template>
  <v-select
    :model-value="modelValue"
    :options="normalizedOptions"
    :reduce="option => option.value"
    :label="'label'"
    :multiple="multiple"
    :placeholder="placeholder"
    :clearable="clearable"
    :disabled="disabled"
    :class="['select2', { 'is-invalid': invalid }]"
    @update:modelValue="$emit('update:modelValue', $event)"
  />
</template>

<script setup>
import { computed } from 'vue'
import VSelect from 'vue-select'

defineOptions({ name: 'Select2' })

const props = defineProps({
  modelValue: {
    type: [String, Number, Array, Object, Boolean],
    default: ''
  },
  options: {
    type: Array,
    default: () => []
  },
  labelKey: {
    type: String,
    default: 'label'
  },
  valueKey: {
    type: String,
    default: 'value'
  },
  multiple: {
    type: Boolean,
    default: false
  },
  placeholder: {
    type: String,
    default: 'Select option'
  },
  clearable: {
    type: Boolean,
    default: true
  },
  disabled: {
    type: Boolean,
    default: false
  },
  invalid: {
    type: Boolean,
    default: false
  }
})

defineEmits(['update:modelValue'])

const normalizedOptions = computed(() =>
  props.options.map((option) => {
    if (option && typeof option === 'object') {
      const label = option.label ?? option[props.labelKey]
      const value = option.value ?? option[props.valueKey]
      return { label, value }
    }
    return { label: String(option), value: option }
  })
)
</script>

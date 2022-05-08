<template>
    <DefaultField :field="field" :errors="errors" :show-help-text="showHelpText">
        <template #field>
            <div class="flex relative w-full">

                <select v-model="value"
                        :id="field.attribute"
                        class="w-full block form-control form-select form-select-bordered"
                        :class="errorClasses"
                        :disabled="disabled">
                    <option :value="null" v-if="loaded && options.length">{{ __('Choose an option') }}</option>
                    <option :value="null" v-if="loaded && options.length == 0">{{ __('No Results') }}</option>
                    <option
                        :key="option.value"
                        :value="option.value"
                        v-for="option in options">
                        {{ option.display }}
                    </option>
                </select>
                <svg class="flex-shrink-0 pointer-events-none form-select-arrow" xmlns="http://www.w3.org/2000/svg"
                     width="10" height="6" viewBox="0 0 10 6">
                    <path class="fill-current"
                          d="M8.292893.292893c.390525-.390524 1.023689-.390524 1.414214 0 .390524.390525.390524 1.023689 0 1.414214l-4 4c-.390525.390524-1.023689.390524-1.414214 0l-4-4c-.390524-.390525-.390524-1.023689 0-1.414214.390525-.390524 1.023689-.390524 1.414214 0L5 3.585786 8.292893.292893z"></path>
                </svg>
            </div>
        </template>
    </DefaultField>
</template>

<script>
import {FormField, HandlesValidationErrors} from 'laravel-nova'
import {walk} from "../utils";

export default {
    mixins: [FormField, HandlesValidationErrors],

    props: ['resourceName', 'resourceId', 'field'],

    data() {
        return {
            options: [],
            loaded: false,
            parentValue: null
        }
    },

    mounted() {
        if (!this.parent_attribute) {
            this.updateOptions();
        }

        this.watchedComponents.forEach(component => {

            let attribute = 'value'

            if (component.field.component === 'belongs-to-field') {
                attribute = 'selectedResource';
            }

            component.$watch(attribute, (value) => {

                this.parentValue = (value && attribute == 'selectedResource') ? value.value : value;

                this.updateOptions();
            }, {immediate: true});
        });
    },

    computed: {
        watchedComponents() {
            if (!this.field.parent_attribute) {
                return [];
            }

            const children = [];
            walk(this.$parent.$.subTree, (item) =>{
                children.push(item);
            })

            return children.filter(component => {
                return this.isWatchingComponent(component);
            })
        },
        endpoint() {
            return this.field.endpoint
                .replace('{resource-name}', this.resourceName)
                .replace('{resource-id}', this.resourceId ? this.resourceId : '')
                .replace('{' + this.field.parent_attribute + '}', this.parentValue ? this.parentValue : '')
        },
        empty() {
            return this.loaded && this.options.length == 0;
        },

        disabled() {
            return this.loaded == false && (this.field.parent_attribute != undefined && this.parentValue == null) || this.options.length == 0;
        }
    },

    methods: {
        setInitialValue() {
            this.value = this.field.value || ''
        },

        fill(formData) {
            formData.append(this.field.attribute, this.value || '')
        },

        updateOptions() {
            this.options = [];
            this.loaded = false;

            if (this.notWatching() || (this.parentValue != null && this.parentValue != '')) {
                Nova.request().get(this.endpoint)
                    .then(response => {
                        this.loaded = true;
                        this.options = response.data;
                        let optionValueExists = false;
                        this.options.forEach(option => {
                            if (option.value == this.value) {
                                optionValueExists = true;
                            }
                        })

                        if (optionValueExists == false) {
                            this.value = null;
                        }
                    })
            }
        },

        notWatching() {
            return this.field.parent_attribute == undefined;
        },

        isWatchingComponent(component) {
            return component.field !== undefined
                && component.field.attribute == this.field.parent_attribute;
        }
    },
}
</script>

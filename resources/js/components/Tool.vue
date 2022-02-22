<template>
    <div>
        <heading class="mb-6">Транзакції Liqpay</heading>

        <div class="flex">
            <div class="w-full flex items-center mb-6">
                <label for="only-success" class="inline-flex items-center">
                    <input v-model="filters.only_success" :true-value="1" :false-value="0" id="only-success" type="checkbox" class="checkbox">
                    <span class="ml-2">
                        Показувати лише успішні
                    </span>
                </label>
            </div>
            <div class="w-full flex items-center mb-6">
                <div class="flex w-full justify-end items-center mx-3"></div>
                <div class="flex-no-shrink ml-auto">
                    <date-time-picker :value="filters.from" @change="filters.from = $event" date-format="d.m.Y" :enableTime="false" placeholder="Від" class="form-control form-input form-input-bordered flatpickr-input" />
                    <date-time-picker :value="filters.to" @change="filters.to = $event" date-format="d.m.Y" :enableTime="false" placeholder="До" class="form-control form-input form-input-bordered flatpickr-input" />
                </div>
            </div>
        </div>

        <logs-list-modal v-if="showLogsOfItem" :logs="showLogsOfItem.logs" @close="showLogsOfItem = null" />

        <div class="card">
            <div class="p-2 text-center">
                <span v-if="isLoading" class="btn btn-sm btn-outline inline-flex items-center focus:outline-none focus:shadow-outline active:outline-none active:shadow-outline">
                    Оновлення даних...
                </span>
                <a v-else href="#" @click.prevent="load" class="btn btn-sm btn-outline inline-flex items-center focus:outline-none focus:shadow-outline active:outline-none active:shadow-outline">
                    Оновити дані
                </a>
            </div>
            <table cellpadding="0" cellspacing="0" data-testid="resource-table" class="table w-full">
                <thead>
                    <tr>
                        <th class="text-center w-8">
                            <span class="inline-flex items-center">
                                ID
                             </span>
                         </th>
                         <th class="text-center">
                            <span class="inline-flex items-center">
                                Сума
                             </span>
                         </th>
                         <th class="text-center">
                            <span class="inline-flex items-center">
                                Статус
                             </span>
                         </th>
                         <th class="text-center">
                            <span class="inline-flex items-center">
                                Банк
                             </span>
                         </th>
                         <th class="text-center">
                            <span class="inline-flex items-center">
                                Дата і час
                             </span>
                         </th>
                         <th class="text-center w-40">
                            <span class="inline-flex items-center">
                                &nbsp;
                             </span>
                         </th>
                     </tr>
                 </thead>
                 <tbody>
                    <tr v-for="item in items" :key="item.id">
                        <td>
                            <div class="text-center">
                                <span class="whitespace-no-wrap">{{ item.id }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="text-center">
                                <span class="whitespace-no-wrap">
                                    <strong class="font-bold">{{ item.amount }} {{ item.currency }}</strong>
                                </span>
                            </div>
                        </td>
                        <td>
                            <div class="text-center">
                                <span class="whitespace-no-wrap" :class="{'font-bold payment-success': item.isSuccess}" :title="item.statusTitle">
                                    {{ item.status || '...' }}
                                </span>
                            </div>
                        </td>
                        <td>
                            <div class="text-center">
                                <span class="whitespace-no-wrap">{{ item.sender_card_bank || '-' }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="text-center">
                                <span class="whitespace-no-wrap">
                                    {{ item.created_at }}
                                </span>
                            </div>
                        </td>
                        <td>
                            <div class="text-right flex justify-end">
                                <span v-if="item.order" class="whitespace-no-wrap mr-2">
                                    <router-link :to="{
                                          name: 'detail',
                                          params: {
                                            resourceName: item.order.resourceName,
                                            resourceId: item.order.morphToId,
                                          },
                                        }"
                                        class="btn btn-sm btn-outline inline-flex items-center focus:outline-none focus:shadow-outline active:outline-none active:shadow-outline max-w-xs truncate"
                                      >
                                        {{ item.order.value}}
                                    </router-link>
                                </span>

                                <span class="whitespace-no-wrap">
                                    <span v-if="!item.logs.length" class="flex items-center select-none bg-30 px-3 py-1 border-2 border-30 rounded max-w-xs truncate">
                                        Логи
                                    </span>
                                    <a v-if="item.logs.length" href="#" @click.prevent="showLogsOfItem = item" class="btn btn-sm btn-outline inline-flex items-center focus:outline-none focus:shadow-outline active:outline-none active:shadow-outline max-w-xs truncate">
                                        Логи: {{ item.logs.length }}
                                    </a>
                                </span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="p-2 text-center" v-if="nextUrl">
                <span v-if="isLoadingNext" class="btn btn-sm btn-outline inline-flex items-center focus:outline-none focus:shadow-outline active:outline-none active:shadow-outline">
                    Завантаження...
                </span>
                <a v-else href="#" @click.prevent="loadNext" class="btn btn-sm btn-outline inline-flex items-center focus:outline-none focus:shadow-outline active:outline-none active:shadow-outline">
                    Завантажити ще
                </a>
            </div>
        </div>
    </div>
</template>

<script>
//
import axios from 'axios'
import LogsListModal from './LogsListModal'

export default {
    components: {
        LogsListModal,
    },
    data () {
        return {
            filters: {
                from: null,
                to: null,
                only_success: 0,
            },
            items: [],
            showLogsOfItem: null,
            nextUrl: null,
            isLoading: false,
            isLoadingNext: false,
        }
    },
    watch: {
        filters: {
            deep: true,
            handler () {
                this.load()
            }
        }
    },
    methods: {
        load() {
            if (this.isLoading) {
                return
            }

            this.isLoading = true

            axios.get('/nova-vendor/laravel-liqpay/payments', {
                params: this.filters
            })
                .then((response) => {
                    this.isLoading = false
                    this.nextUrl = response.data.links.next
                    this.items = response.data.data
                })
                .catch((err) => {
                    this.isLoading = false
                    console.error(err)
                })
        },
        loadNext() {
            if (this.isLoadingNext) {
                return
            }

            this.isLoadingNext = true

            axios.get(this.nextUrl)
                .then((response) => {
                    this.isLoadingNext = false
                    this.nextUrl = response.data.links.next
                    this.items = this.items.concat(response.data.data)
                })
                .catch((err) => {
                    this.isLoadingNext = false
                    console.error(err)
                })
        },
    },
    mounted() {
        this.load()
    },
}
</script>

<style>
    .payment-success {
        color: #369e1c;
    }
</style>

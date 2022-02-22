<template>
    <modal @modal-close="$emit('close')" tabindex="-1" role="dialog">

        <log-details-modal @close="logDetails = null" v-if="logDetails" :log="logDetails" />

        <div
          class="bg-40 rounded-lg shadow-lg overflow-hidden p-8"
          style="width: 800px"
        >

          <div class="flex justify-between">
              <h1 class="mb-6 text-90 font-normal text-2xl">Логи звернень від банку</h1>
              <a href="#" @click.prevent="$emit('close')" class="text-80 hover:text-90">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
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
                    <th class="text-center w-8">
                        <span class="inline-flex items-center">
                            Тип
                         </span>
                    </th>
                    <th class="text-center w-8">
                        <span class="inline-flex items-center">
                            Успішна взаємодія
                         </span>
                    </th>
                    <th class="text-center w-8">
                        <span class="inline-flex items-center">
                            Причина
                         </span>
                    </th>
                    <th class="text-center w-8">
                        <span class="inline-flex items-center">
                            Дата
                         </span>
                    </th>
                    <th class="text-center w-8">
                        <span class="inline-flex items-center">
                            &nbsp;
                         </span>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="log in logs" :key="log.id">
                    <td>
                        <div class="text-center">
                            <span class="whitespace-no-wrap">{{ log.id }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="text-center">
                            <span class="whitespace-no-wrap">{{ log.type }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="text-center">
                            <span v-if="log.is_successfuly_handled" style="color: #369e1c;" class="whitespace-no-wrap font-bold">Так</span>
                            <span v-else class="whitespace-no-wrap">Ні</span>
                        </div>
                    </td>
                    <td>
                        <div class="text-center">
                            <span class="whitespace-no-wrap">{{ log.fail_reason || '-' }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="text-center">
                            <span class="whitespace-no-wrap">{{ log.created_at }}</span>
                        </div>
                    </td>
                    <td class="text-right">
                        <a href="#" @click.prevent="logDetails = log" class="btn btn-sm btn-outline inline-flex items-center focus:outline-none focus:shadow-outline active:outline-none active:shadow-outline">
                            Деталі
                        </a>
                    </td>
                </tr>
            </tbody>
        </table>
        </div>
    </modal>
</template>

<script>
    import LogDetailsModal from './LogDetailsModal'
    export default {
        components: {
            LogDetailsModal,
        },
        data() {
            return {
                logDetails: null,
            }
        },
        props: {
            logs: {
                type: Array,
                require: true,
            }
        }
    }
</script>

<template>
    <modal @modal-close="$emit('close')" tabindex="-1" role="dialog">
        <div
          class="bg-40 rounded-lg shadow-lg overflow-hidden p-8"
          style="width: 900px"
        >
          <div class="flex justify-between">
              <h1 class="mb-6 text-90 font-normal text-2xl">Деталі</h1>
              <a href="#" @click.prevent="$emit('close')" class="text-80 hover:text-90">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
              </a>
          </div>
            <detail-code-field :field="{
                value: log.payload,
                name: 'Payload',
                options: {mode: 'application/json'}
            }" />
        </div>
    </modal>
</template>

<script>
    export default {
        props: {
            log: {
                type: Object,
                require: true,
            }
        }
    }
</script>

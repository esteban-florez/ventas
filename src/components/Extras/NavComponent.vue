<template>
  <nav class="level mt-2">
    <div class="level-left">
      <div class="level-item">
        <p class="title is-1">{{ titulo }}</p>
      </div>
    </div>

    <div class="level-right" v-if="link || boton">
      <p class="level-item">
        <b-button class="is-rounded" type="is-primary" size="is-large" icon-left="plus" :tag="boton ? 'button' : 'router-link'" :to="link" @click="$emit('click')">
          {{ texto }}
        </b-button>
      </p>
    </div>
  </nav>
</template>
<script>
export default {
  name: "NavComponent",
  props: ["titulo", "link", "texto", "boton"],
}
</script>

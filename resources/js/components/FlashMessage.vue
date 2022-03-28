<template>
  <div class="col-sm-12">
    <Transition name="slide-fade">
      <div id="focus" class="top-message" v-if="message">
        <div role="alert" v-if="message"
        :class="{
          'alert alert-warning alert-dismissible success-alert alert-dismissible': message.type === 'success',
          'alert alert-warning alert-dismissible error-alert alert-dismissible': message.type === 'error',
        }"><img src="images/check-mark.png" class="check_mark">{{ message.text }}
          <button type="button" data-dismiss="alert" aria-label="Close" class="close" @click.prevent="message = null"><span aria-hidden="true">✕</span></button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script>
export default {
  data() {
    return {
      message: null,
    };
  },
  mounted() {
    let timer;
    Bus.$on('flash-message', (message) => {
      clearTimeout(timer);

      this.message = message;

      timer = setTimeout(() => {
        this.message = null;
      }, 5000);
    });
  },
};
</script>

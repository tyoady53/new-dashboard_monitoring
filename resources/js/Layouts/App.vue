<template>
    <div class="c-app">
      <div class="c-wrapper c-fixed-components">

        <!-- header -->
        <Header />
        <!-- end header -->

        <div class="c-body">

          <!-- content -->
          <slot />

          <!-- end content -->

        </div>
        <footer class="c-footer">
        <div>&copy; {{ new Date().getFullYear() }} - PT. WYNACOM UNITAMA SEJAHTERA</div>
        </footer>
      </div>
    </div>
  </template>

  <script>

    import Header from '../Components/Header.vue'

    export default {

      //register components
      components: {
        Header
      },

    }
  </script>

<style>
.chart {
    height: 330px;
}

.switch {
  position: relative;
  display: inline-block;
  width: 30px;
  height: 17px;
}

.switch input {
  display: none;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: 0.4s;
  transition: 0.4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 13px;
  width: 13px;
  left: 2px;
  bottom: 2px;
  background-color: white;
  -webkit-transition: 0.4s;
  transition: 0.4s;
}

input:checked + .slider {
    background: rgb(137, 75, 157);
}

input:focus + .slider {
  box-shadow: 0 0 1px #101010;
}

input:checked + .slider:before {
  -webkit-transform: translateX(13px);
  -ms-transform: translateX(13px);
  transform: translateX(13px);
}

.slider.round {
  border-radius: 17px;
}

.slider.round:before {
  border-radius: 50%;
}
</style>

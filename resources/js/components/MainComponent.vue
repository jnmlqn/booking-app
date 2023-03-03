<template>
    <div class="row" v-if="!isLogged">
        <div class="col-6">
            <login-component></login-component>
        </div>
        <div class="col-6">
            <registration-component></registration-component>
        </div>
    </div>

    <div v-else-if="isLogged">
        <home-component></home-component>
    </div>
</template>

<script>
    export default {
        mounted() {
            window.api.post('me')
            .then((response) => {
            })
            .catch((error) => {
                this.isLogged = false;
                document.cookie = `accessToken=`;
            });

            this.$root.$on('authorization', (isLogged) => {
                this.isLogged = isLogged;
            });
        },

        data() {
            let isLogged = false;

            let cookie = {};

            document.cookie.split(';')
            .forEach(function(el) {
                let [key,value] = el.split('=');
                cookie[key.trim()] = value;
            });

            if (cookie['accessToken'] !== null && cookie['accessToken'] !== undefined && cookie['accessToken'] !== '') {
                isLogged = true;
            }

            return {
                isLogged
            }
        }
    }
</script>

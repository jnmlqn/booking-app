<template>
    <div class="px-4" v-if="name !== null && name !== '' && name !== undefined">
        Welcome, {{ this.name }} - <a href="#" class="text-primary" @click.prevent="logout">Logout</a>
    </div>
</template>

<script>
    export default {
        mounted() {
            this.displayName();

            this.$root.$on('authorization', () => {
                console.log(1)
                this.displayName();
            });
        },

        data() {
            return {
                name: null
            }
        },

        methods: {
            displayName() {
                let cookie = {};
                document.cookie.split(';')
                .forEach(function(el) {
                    let [key,value] = el.split('=');
                    cookie[key.trim()] = value;
                });
                this.name = cookie.userName;
            },

            logout() {
                document.cookie = `accessToken=`;
                document.cookie = `userName=`;
                this.name = null;
                this.$root.$emit('authorization', false);
            }
        }
    }
</script>

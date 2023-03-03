<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Login</div>
                    <div class="card-body">
                        <form>
                            <label>Email</label>
                            <input type="email" class="form-control" v-model="email">
                            <p></p>

                            <label>Password</label>
                            <input type="password" class="form-control" v-model="password">
                            <p></p>

                            <button
                                class="btn btn-success"
                                id="btn-login"
                                @click.prevent="login"
                            >
                                Login
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                email: '',
                password: '',
            }
        },

        methods: {
            login() {
                const payload = {
                    email: this.email,
                    password: this.password,
                }

                window.api.post('login', payload)
                .then(({data: {data, message}}) => {
                    this.$toastr.s(message);
                    document.cookie = `accessToken=${data.access_token}`;
                    document.cookie = `userName=${data.name}`;
                    this.$root.$emit('authorization', true);
                })
                .catch((error) => {
                    switch(error.response.status) {
                        case 401:
                            this.$toastr.e('Invalid email or password');
                            break;
                        case 422:
                            const validationMessage = error.response.data.data;

                            let html = ``;

                            for (const key in validationMessage) {
                                html += key;
                                html += '<ul>'

                                for (const msg of validationMessage[key]) {
                                    html += `<li>${msg}</li>`
                                }

                                html += '</ul>'
                            }

                            this.$toastr.w(html);

                            break;

                        default:
                            this.toastr.e(error.response.statusText);
                    }
                });
            }
        }
    }
</script>

<style scoped>
    #btn-login {
        float: right;
    }
</style>

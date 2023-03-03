<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Registration</div>
                    <div class="card-body">
                        <form>
                            <label>Name</label>
                            <input type="text" class="form-control" v-model="name">
                            <p></p>

                            <label>Email</label>
                            <input type="email" class="form-control" v-model="email">
                            <p></p>

                            <label>Password</label>
                            <input type="password" class="form-control" v-model="password">
                            <p></p>

                            <label>Re-type Password</label>
                            <input type="password" class="form-control" v-model="retype">
                            <p></p>

                            <button
                                class="btn btn-success"
                                id="btn-register"
                                @click.prevent="register"
                            >
                                Register
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
                name: '',
                email: '',
                password: '',
                retype: ''
            }
        },

        methods: {
            register() {
                if (this.password !== this.retype) {
                    this.$toastr.w('Passwords do not match');
                    return;
                }

                const payload = {
                    name: this.name,
                    email: this.email,
                    password: this.password,
                }

                window.api.post('register', payload)
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
    #btn-register {
        float: right;
    }
</style>

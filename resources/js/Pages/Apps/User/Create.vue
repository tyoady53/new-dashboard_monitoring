<template>
    <Head>
        <title>Add New Users - Master Form</title>
    </Head>
    <main class="c-main">
        <div class="container-fluid">
            <div class="fade-in">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card border-0 rounded-3 shadow border-top-purple">
                            <div class="card-header">
                                <span class="font-weight-bold"><i class="fa fa-shield-alt"></i> ADD USER</span>
                            </div>
                            <div class="card-body">
                                <form @submit.prevent="submit">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="fw-bold">Full Name</label>
                                                <input class="form-control" v-model="form.name" :class="{ 'is-invalid': errors.name }" type="text" placeholder="Full Name">
                                            </div>
                                            <div v-if="errors.name" class="alert alert-danger">
                                            {{ errors.name }}
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="fw-bold">Email</label>
                                                <input class="form-control" v-model="form.email" :class="{ 'is-invalid': errors.email }" type="email" placeholder="Email">
                                            </div>
                                            <div v-if="errors.email" class="alert alert-danger">
                                            {{ errors.email }}
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="fw-bold">Username</label>
                                                <input class="form-control" v-model="form.username" :class="{ 'is-invalid': errors.username }" type="text" placeholder="Username">
                                            </div>
                                            <div v-if="errors.username" class="alert alert-danger">
                                            {{ errors.username }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="fw-bold">Password</label>
                                                <input class="form-control" v-model="form.password" :class="{ 'is-invalid': errors.password }" type="password" placeholder="Password">
                                            </div>
                                            <div v-if="errors.password" class="alert alert-danger">
                                                {{ errors.password }}
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="fw-bold">Repeat Password</label>
                                                <input class="form-control" v-model="form.password_confirmation" :class="{ 'is-invalid': errors.password_confirmation }" type="password" placeholder="Repeat Password">
                                            </div>
                                            <div v-if="errors.password_confirmation" class="alert alert-danger">
                                                {{ errors.password_confirmation }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="fw-bold">Company</label>
                                            <select class="form-control" v-model="form.company" @change="getBranch">
                                                <option v-for="company in companies" :value="company.id" name="division">{{ company.customer_name }}</option>
                                            </select>
                                            <div v-if="errors.company" class="alert alert-danger">
                                                {{ errors.company }}
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="fw-bold">Branch</label>
                                            <select class="form-control" v-model="form.branch" @change="getBranch">
                                                <option v-for="branch in branches" :value="branch.id" name="division">{{ branch.branch_name }}</option>
                                            </select>
                                            <div v-if="errors.branch" class="alert alert-danger">
                                                {{ errors.branch }}
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="mb-3">
                                        <label class="fw-bold">Role</label>
                                        <br>
                                        <div class="form-check form-check-inline" v-for="(role, index) in roles" :key="index">
                                            <input class="form-check-input" type="checkbox" v-model="form.roles" :value="role.name" :id="`check-${role.id}`">
                                            <label class="form-check-label" :for="`check-${role.id}`">{{ role.name }}</label>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-12">
                                                <button class="btn btn-primary shadow-sm rounded-sm" type="submit">SAVE</button>
                                                <button class="btn btn-warning shadow-sm rounded-sm ms-3" type="reset">RESET</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
    import LayoutApp from '../../../Layouts/App.vue';

    import { Head, Link } from '@inertiajs/inertia-vue3';

    import { reactive } from 'vue';

    import { Inertia } from '@inertiajs/inertia';

    import Swal from 'sweetalert2';

    import axios from "axios";

    export default {

        layout: LayoutApp,

        components: {
            Head,
            Link
        },

        props: {
            errors: Object,
            roles: Array,
            companies:Array,
        },

        data: () => ({
            branches : [],
            UrlOrigin : window.location.origin,
        }),

        methods: {
            getBranch() {
                axios
                .get(this.UrlOrigin + `/api/dashboard/get_branch/` + this.form.company)
                .then((response) => {
                    this.branches = response.data.data;
                })
                .catch((error) => {
                    console.log(error.response.data);
                });
            },
        },

        setup() {
            const form = reactive({
                name: '',
                email: '',
                password: '',
                company: '',
                username: '',
                branch: '',
                roles:[],
                password_confirmation: '',
            });

            const submit = () => {
                Inertia.post('/user', {
                    name: form.name,
                    email: form.email,
                    password: form.password,
                    user_name: form.username,
                    company: form.company,
                    branch: form.branch,
                    roles: form.roles,
                    password_confirmation: form.password_confirmation,
                }, {
                    onSuccess: () => {
                        Swal.fire({
                            title: 'Success!',
                            text: 'User saved successfully.',
                            icon: 'success',
                            showConfirmButton: false,
                            timer: 2000
                        });
                    },
                });
            }
            return {
                form,
                submit,
            };
        }
    }
</script>

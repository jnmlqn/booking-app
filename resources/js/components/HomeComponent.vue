<template>
    <div class="container">
        <div class="container row">
            <p id="page-title" v-if="isEditing">Edit your room reservation</p>
            <p id="page-title" v-else-if="!isEditing">Book a meeting room now!</p>

            <div class="col-lg-3 col-md-12">
                <label>Select Date and Time</label>
                <DatePicker mode="dateTime" v-model="date" :min-date='new Date()'></DatePicker>
            </div>
            <div class="col-lg-4 col-md-12">
                <label>Select room</label>
                <select class="form-select" v-model="roomId">
                    <option value="" selected>Select room</option>
                    <option :value="room.id" v-for="room in rooms">
                        {{ room.roomName }}
                    </option>
                </select>

                <p></p>
                <p></p>

                <label>Select meeting duration (minutes)</label>
                <input type="number" class="form-control" min="15" max="60" v-model="duration">

                <p></p>

                <button
                    class="btn btn-success"
                    id="btn-availability"
                    @click="checkAvailability()"
                >
                    Check availability
                </button>

                <button
                    class="btn btn-primary"
                    id="btn-reserve"
                    v-if="booking && isAvailable && !isEditing"
                    @click="reserveRoom()"
                >
                    Book this room
                </button>

                <p></p>

                <p class="text-danger" v-if="booking && !isAvailable">
                    The room is not available for the selected date and time. Consider changing the date and time or the room.
                </p>

                <p class="text-success" v-if="booking && isAvailable">
                    The room is available for the selected date and time.
                </p>
            </div>
        </div>

        <p></p>
        <p></p>

        <div class="container">
            <button class="btn btn-primary" id="btn-export" @click.prevent="exportData">
                Export as CSV
            </button>

            <p><strong>Your room reservations</strong></p>

            <table class="table table-striped border mt-4">
                <thead>
                    <tr>
                        <th>Room Name</th>
                        <th>Date</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="reservation in reservations">
                        <td>{{ reservation.roomName }}</td>
                        <td>{{ reservation.reservationDate }}</td>
                        <td>{{ reservation.startTime }}</td>
                        <td>{{ reservation.endTime }}</td>
                        <td>{{ reservation.status }}</td>
                        <td>
                            <button 
                                class="btn btn-primary"
                                @click.prevent="editReservation(reservation)"
                                v-if="reservation.status == 'Incoming' && !isEditing"
                            >
                                Edit
                            </button>

                            <button
                                class="btn btn-success"
                                id="btn-update"
                                v-if="isEditing && reservationIdToEdit == reservation.id"
                                :disabled="!isAvailable"
                                @click="updateReservation()"
                            >
                                Save
                            </button>

                            <button
                                class="btn btn-danger"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteModal"
                                @click.prevent="deleteReservationModal(reservation.id)"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Delete Reservation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Are you sure to delete this reservation? This can't be undone!
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal" @click.prevent="deleteReservation">Yes</button>
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">No</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import { DatePicker } from 'v-calendar';

    export default {
        components: {
            DatePicker
        },

        mounted() {
            this.getRooms();
            this.getReservations();
        },

        data() {
            return {
                date: new Date(),
                duration: 15,
                roomId: '',
                rooms: [],
                isAvailable: false,
                booking: false,
                reservations: [],
                reservationIdToDelete: null,
                reservationIdToEdit: null,
                isEditing: false,
            }
        },

        methods: {
            getRooms() {
                window.api.post('rooms')
                .then(({data : {data, message}}) => {
                    this.rooms = data.rooms;
                })
            },

            getReservations() {
                window.api.get('reservations')
                .then(({data : {data}}) => {
                    this.reservations = data.reservations;
                })
            },

            formatDate(datetime) {
                const months = ["01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12"];
                return datetime.getFullYear() + '-' + months[datetime.getMonth()] + '-' + (datetime.getDate() < 10 ? `0${datetime.getDate()}` : datetime.getDate());
            },

            formatTime(datetime) {
                let h = datetime.getHours();
                let m = datetime.getMinutes();
                let hours = h < 10 ? '0'+h : h;
                let minutes = m < 10 ? '0'+m : m;
                return hours+':'+minutes;
            },

            handleErrorMessage(error) {
                switch(error.response.status) {
                    case 401:
                        this.$toastr.e('Session expired');
                        this.$root.$emit('authorization', false);
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
            },

            checkAvailability() {
                if (this.roomId === '') {
                    this.$toastr.w('Please select a room');
                    return;
                }

                const date = this.formatDate(this.date);
                const startTime = this.formatTime(this.date);

                window.api.post('rooms/availability', {
                    date: date,
                    startTime: startTime,
                    duration: this.duration,
                    roomId: this.roomId,
                    reservationId: this.reservationIdToEdit,
                })
                .then(({data: {data, message}}) => {
                    this.booking = true;
                    this.isAvailable = data.isAvailable;
                    this.$toastr.s(message);
                })
                .catch((error) => {
                    this.handleErrorMessage(error);
                });
            },

            reserveRoom() {
                if (this.roomId === '') {
                    this.$toastr.w('Please select a room');
                    return;
                }

                const date = this.formatDate(this.date);
                const startTime = this.formatTime(this.date);

                window.api.post('reservations', {
                    date: date,
                    startTime: startTime,
                    duration: this.duration,
                    roomId: this.roomId,
                })
                .then(({data: {data, message}}) => {
                    this.booking = false;
                    this.isAvailable = false;
                    this.roomId = '';
                    this.date = new Date();
                    this.duration = 15;
                    this.$toastr.s(message);
                    this.getReservations();
                })
                .catch((error) => {
                    this.handleErrorMessage(error);
                });
            },

            deleteReservationModal(id) {
                this.reservationIdToDelete = id;
            },

            deleteReservation() {
                window.api.delete(`reservations/${this.reservationIdToDelete}`)
                .then(({data: {message}}) => {
                    this.reservationIdToDelete = null;
                    this.getReservations();
                    this.$toastr.s(message);
                })
                .catch((error) => {
                    this.handleErrorMessage(error);
                });
            },

            editReservation(reservation) {
                this.isEditing = true;
                this.$toastr.i(`Editing reservation id: ${reservation.id}`);
                this.reservationIdToEdit = reservation.id;
                this.date = reservation.startDateTime;
                this.duration = reservation.duration;
                this.roomId = reservation.roomId;
            },

            updateReservation() {
                if (this.roomId === '') {
                    this.$toastr.w('Please select a room');
                    return;
                }

                const date = this.formatDate(this.date);
                const startTime = this.formatTime(this.date);

                window.api.put(`reservations/${this.reservationIdToEdit}`, {
                    date: date,
                    startTime: startTime,
                    duration: this.duration,
                    roomId: this.roomId,
                })
                .then(({data: {data, message}}) => {
                    this.booking = false;
                    this.isAvailable = false;
                    this.isEditing = false;
                    this.roomId = '';
                    this.date = new Date();
                    this.duration = 15;
                    this.$toastr.s(message);
                    this.reservationIdToEdit = null;
                    this.getReservations();
                })
                .catch((error) => {
                    this.handleErrorMessage(error);
                });
            },

            exportData() {
                window.api.get('export/reservations')
                .then((response) => {
                    var blob = new Blob(
                        [
                            response.data
                        ],
                        {
                            type:'text/csv'
                        }
                    );
                    window.location.href = (URL.createObjectURL(blob));
                })
                .catch((error) => {
                    this.handleErrorMessage(error);
                });
            }
        }
    }
</script>

<style scoped>
    #page-title {
        font-size: 2em;
    }

    #btn-export {
        float: right;
    }
</style>

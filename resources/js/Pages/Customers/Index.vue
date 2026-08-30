<script setup>
import { h, ref, watch } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import { createColumnHelper } from '@tanstack/vue-table'
import { PencilIcon, Trash2Icon, PlusIcon, BuildingIcon } from '@lucide/vue'
import Default from '@js/Layouts/Default.vue'
import PageHeader from '@js/Components/Common/PageHeader.vue'
import Datatable from '@js/Components/Common/Datatable.vue'
import Sheet from '@js/Components/Notifications/Sheet.vue'
import Modal from '@js/Components/Notifications/Modal.vue'
import FormInput from '@js/Components/Forms/FormInput.vue'
import RowActions from '@js/Components/Common/RowActions.vue'
import Button from '@js/Components/Button.vue'

defineOptions({
    layout: Default,
})

const props = defineProps({
    customers: { type: Object, required: true },
})

const columnHelper = createColumnHelper()
const loading = ref(false)
const pagination = ref({
    current_page: props.customers.current_page,
    per_page: Number(props.customers.per_page),
    total: props.customers.total,
})

// ------ Create Sheet ------
const showCreateSheet = ref(false)
const createForm = useForm({
    name: '',
    email: '',
    phone: '',
    company: '',
})

const openCreateSheet = () => {
    createForm.reset()
    showCreateSheet.value = true
}

const submitCreate = () => {
    createForm.post(route('admin.customer.store'), {
        onSuccess: () => {
            showCreateSheet.value = false
            createForm.reset()
        },
    })
}

// ------ Edit Sheet ------
const showEditSheet = ref(false)
const customerBeingEdited = ref(null)
const editForm = useForm({ name: '', email: '', phone: '', company: '' })

const openEditSheet = (customer) => {
    customerBeingEdited.value = customer
    editForm.name = customer.name
    editForm.email = customer.email
    editForm.phone = customer.phone ?? ''
    editForm.company = customer.company ?? ''
    showEditSheet.value = true
}

const submitEdit = () => {
    editForm.put(route('admin.customer.update', customerBeingEdited.value.id), {
        onSuccess: () => {
            showEditSheet.value = false
        },
    })
}

// ------ Delete Modal ------
const showDeleteModal = ref(false)
const customerToDelete = ref(null)

const openDeleteModal = (customer) => {
    customerToDelete.value = customer
    showDeleteModal.value = true
}

const confirmDelete = () => {
    router.delete(route('admin.customer.destroy', customerToDelete.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false
            customerToDelete.value = null
        },
    })
}

// ------ Table Columns ------
const columns = [
    columnHelper.accessor('name', {
        header: 'Name',
        cell: info =>
            h('span', { class: 'text-sm font-medium text-foreground' }, info.getValue()),
    }),
    columnHelper.accessor('email', {
        header: 'Email',
        cell: info =>
            h('span', { class: 'text-sm text-muted-foreground' }, info.getValue()),
    }),
    columnHelper.accessor('company', {
        header: 'Company',
        cell: info =>
            h('span', { class: 'text-sm text-muted-foreground' }, info.getValue() || '-'),
    }),
    columnHelper.accessor('phone', {
        header: 'Phone',
        meta: { narrow: true },
        cell: info =>
            h('span', { class: 'text-sm text-muted-foreground' }, info.getValue() || '-'),
    }),
    columnHelper.display({
        id: 'actions',
        header: '',
        meta: { narrow: true },
        cell: info => {
            const customer = info.row.original
            return h(RowActions, {
                actions: [
                    {
                        label: 'Edit',
                        icon: PencilIcon,
                        onClick: () => openEditSheet(customer),
                    },
                    {
                        label: 'Delete',
                        icon: Trash2Icon,
                        variant: 'danger',
                        onClick: () => openDeleteModal(customer),
                    },
                ],
            })
        },
    }),
]

// ------ Pagination ------
watch(
    pagination,
    p => {
        loading.value = true
        router.get(
            route('admin.customer.index'),
            { page: p.current_page, per_page: Number(p.per_page) },
            {
                preserveState: true,
                preserveScroll: true,
                onFinish: () => (loading.value = false),
            }
        )
    },
    { deep: true }
)
</script>

<template>
    <Head title="Customers" />

    <main class="mx-auto max-w-5xl" aria-labelledby="customers-heading">
        <PageHeader
            title="Customers"
            :breadcrumbs="[
                { label: 'Dashboard', href: route('dashboard') },
                { label: 'Customers' },
            ]">
            <template #actions>
                <Button id="create-customer-btn" @click="openCreateSheet">
                    <PlusIcon class="mr-2 size-4" />
                    New Customer
                </Button>
            </template>
        </PageHeader>

        <Datatable
            :data="customers.data"
            :columns="columns"
            :loading="loading"
            :pagination="pagination"
            empty-message="No customers yet"
            empty-description="Add your first customer with the button above."
            export-file-name="customers"
            @update:pagination="pagination = $event" />
    </main>

    <!-- Create Sheet -->
    <Sheet v-model:open="showCreateSheet" title="New Customer" description="Fill in the details to add a new customer.">
        <form class="space-y-4" @submit.prevent="submitCreate">
            <FormInput
                id="create-name"
                v-model="createForm.name"
                label="Full Name"
                placeholder="John Doe"
                :error="createForm.errors.name"
                required />
            <FormInput
                id="create-email"
                v-model="createForm.email"
                label="Email"
                type="email"
                placeholder="john@example.com"
                :error="createForm.errors.email"
                required />
            <FormInput
                id="create-phone"
                v-model="createForm.phone"
                label="Phone"
                type="tel"
                placeholder="+1 555 000 0000"
                :error="createForm.errors.phone" />
            <FormInput
                id="create-company"
                v-model="createForm.company"
                label="Company"
                placeholder="Acme Corp"
                :error="createForm.errors.company" />

            <div class="flex justify-end gap-2 pt-2">
                <Button type="button" variant="outline" @click="showCreateSheet = false">
                    Cancel
                </Button>
                <Button type="submit" :disabled="createForm.processing">
                    {{ createForm.processing ? 'Saving...' : 'Save Customer' }}
                </Button>
            </div>
        </form>
    </Sheet>

    <!-- Edit Sheet -->
    <Sheet v-model:open="showEditSheet" title="Edit Customer" description="Update the customer's information.">
        <form class="space-y-4" @submit.prevent="submitEdit">
            <FormInput
                id="edit-name"
                v-model="editForm.name"
                label="Full Name"
                :error="editForm.errors.name"
                required />
            <FormInput
                id="edit-email"
                v-model="editForm.email"
                label="Email"
                type="email"
                :error="editForm.errors.email"
                required />
            <FormInput
                id="edit-phone"
                v-model="editForm.phone"
                label="Phone"
                type="tel"
                :error="editForm.errors.phone" />
            <FormInput
                id="edit-company"
                v-model="editForm.company"
                label="Company"
                :error="editForm.errors.company" />

            <div class="flex justify-end gap-2 pt-2">
                <Button type="button" variant="outline" @click="showEditSheet = false">
                    Cancel
                </Button>
                <Button type="submit" :disabled="editForm.processing">
                    {{ editForm.processing ? 'Saving...' : 'Save Changes' }}
                </Button>
            </div>
        </form>
    </Sheet>

    <!-- Delete Confirmation Modal -->
    <Modal
        v-model:open="showDeleteModal"
        title="Delete Customer"
        :description="`Are you sure you want to delete ${customerToDelete?.name}? This action can be undone from the database.`"
        confirm-label="Delete"
        confirm-variant="danger"
        @confirm="confirmDelete"
        @cancel="showDeleteModal = false" />
</template>

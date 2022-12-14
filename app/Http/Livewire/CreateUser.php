<?php

namespace App\Http\Livewire;

use App\Http\Controllers\Services\UserService;
use App\Models\User;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class CreateUser extends Component
{
    use LivewireAlert;
    protected $listeners = ['getBranches'];
    public $name;
    public $email;
    public $phone;
    public $password;
    public $advertiser_number = '';

    public $branches_ids = [];
    public $user_type = 'admin';
    public $group_permissions = [];
    public $manage_mediators;
    public $can_send_sms_collection;
    public $can_send_sms_individually;
    public $can_show_orders;
    public $can_add_orders;
    public $can_edit_orders;
    public $can_cancel_orders;
    public $user_status;

    public $info = 'active';
    public $permissions;

    public $is_officer = false;

    public function render()
    {
        return view('livewire.create-user');
    }

    public function step($form)
    {
        if ($form == 'permissions') {
            $this->validate();
        }

        $this->info = '';
        $this->permissions = '';

        if ($form == 'info') {
            $this->info = 'active';
            $this->emit('refreshSelect2');
        }

        if ($form == 'permissions') {
            $this->permissions = 'active';
            $this->emit('refreshSelect2');
        }
    }

    protected function rules()
    {
        $fields = [
            'name' => ['required'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['required', 'unique:users,phone', 'regex:/(0)[0-9]{9}/'],
            'password' => ['required'],
        ];

        if ($this->permissions) {
            $fields['branches_ids'] = ['required'];
            $fields['user_type'] = ['required'];
            $fields['group_permissions'] = ['nullable'];
            $fields['user_status'] = ['nullable'];
        }

        return $fields;
    }

    protected function messages()
    {
        $fields = [
            'name.required' => 'هذا الحقل مطلوب',
            'email.required' => 'هذا الحقل مطلوب',
            'email.email' => 'يرجى التأكد من صيغة الايميل',
            'phone.required' => 'هذا الحقل مطلوب',
            'password.required' => 'هذا الحقل مطلوب',
            'phone.unique' => 'هذا الرقم مستخدم من قبل',
            'phone.regex' => 'رقم الهاتف يتكون من 10 ارقام ويبدأ ب 05',
            'branches_ids.required' => 'هذا الحقل مطلوب',
            'email.unique' => 'هذا الايميل مستخدم من قبل',
        ];

        if ($this->permissions) {
            $fields['user_type.required'] = 'هذا الحقل مطلوب';
        }

        return $fields;
    }

    public function updated($propertyName)
    {
        if ($propertyName == "user_type") {
            if ($this->user_type == "office") {
                $this->is_officer = true;
            } else {
                $this->is_officer = false;
            }
        }

        if ($propertyName == 'manage_mediators') {
            $this->setPermission($this->manage_mediators, 'mediators_page');
        }

        if ($propertyName == 'can_send_sms_collection') {
            $this->setPermission($this->can_send_sms_collection, 'send_collection_messages');
        }

        if ($propertyName == 'can_send_sms_individually') {
            $this->setPermission($this->can_send_sms_individually, 'send_individual_messages');
        }

        if ($propertyName == 'can_show_orders') {
            $this->setPermission($this->can_show_orders, 'can_show_order');
        }

        if ($propertyName == 'can_add_orders') {
            $this->setPermission($this->can_add_orders, 'can_create_order');
        }

        if ($propertyName == 'can_edit_orders') {
            $this->setPermission($this->can_edit_orders, 'can_edit_order');
        }

        if ($propertyName == 'can_cancel_orders') {
            $this->setPermission($this->can_cancel_orders, 'can_change_order_status');
        }

        $this->validateOnly($propertyName);
    }

    public function setPermission($check, $permission)
    {
        if ($check) {
            array_push($this->group_permissions, $permission);
        } else {
            if (($key = array_search($permission, $this->group_permissions)) !== false) {
                unset($this->group_permissions[$key]);
            }
        }
    }

    public function getBranches($branches_ids)
    {
        $this->branches_ids = $branches_ids;
    }

    public function store(UserService $userService)
    {
        $data = $this->validate();

        if ($this->is_officer) {
            if ($this->advertiser_number) {
                $this->validate(['advertiser_number' => 'required'], ['advertiser_number.required' => 'هذا الحقل مطلوب!!']);
            }
        }

        if (!$this->advertiser_number) {
            $data['advertiser_number'] = null;
        } else {
            $data['advertiser_number'] = $this->advertiser_number;
        }

        $userService->store($data);
        return redirect()->route('panel.users')->with('message', 'لقد تم انشاء المستخدم بنجاح 👍');
    }
}

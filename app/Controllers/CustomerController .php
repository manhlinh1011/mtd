<?php namespace App\Controllers;

use App\Models\CustomerModel;

class CustomerController extends BaseController
{
    protected $customerModel;

    public function __construct()
    {
        $this->customerModel = new CustomerModel();
    }

    public function index()
    {
        $data['customers'] = $this->customerModel->findAll();
        return view('customers/index', $data);
    }

    public function create()
    {
        if ($this->request->getMethod() === 'post') {
            $this->customerModel->save($this->request->getPost());
            return redirect()->to('/customers');
        }
        return view('customers/create');
    }

    public function edit($id)
    {
        $data['customer'] = $this->customerModel->find($id);
        if ($this->request->getMethod() === 'post') {
            $this->customerModel->update($id, $this->request->getPost());
            return redirect()->to('/customers');
        }
        return view('customers/edit', $data);
    }

    public function delete($id)
    {
        $this->customerModel->delete($id);
        return redirect()->to('/customers');
    }
}

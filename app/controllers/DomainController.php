<?php

class DomainController extends \BaseController {

    /**
     * Instantiate a new DomainController instance.
     */
    public function __construct() {

        $this->beforeFilter('auth');
        $this->beforeFilter('auth.manager');

    }

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function getIndex()
	{
        $domains = Auth::user()->status ==2 ? Domain::all() : Domain::where('user_id',Auth::user()->id)->get();

        return View::make('domain.index')->with('domains', $domains);
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function getAdd()
	{
        if(Auth::user()->status == 2){
            return Redirect::to('/domain');
        }

        if(Domain::where('user_id',Auth::user()->id)->count() >= Config::get('settings.domain_limit')){
            return Output::push(array(
                'path' => 'domain',
                'messages' => array('fail' => _('You have reached limit domain')),
            ));
        }

        return View::make('domain.create');
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function postStore()
	{
        $input = Input::only('domain', 'description');
        $input['prefix'] = $this->generate_prefix();

        $rules = array(
            'domain' => 'required|unique:domains,domain,NULL,id,deleted_at,NULL',
            'prefix' => 'unique:domains,prefix',
        );
        $v = Validator::make($input, $rules);
        if ($v->fails()) {
            return Output::push(array('path' => 'domain/add', 'errors' => $v, 'input' => TRUE));
        }

        $domain = new Domain([
            'id' => md5($input['domain'].time()),
            'user_id' => Auth::user()->id,
            'domain' => $input['domain'],
            'prefix' => $input['prefix'],
            'description' => $input['description'],
        ]);
        $domain->save();
        Event::fire('logger',array(array('domain_add', array('id'=>$domain->id, 'domain_name'=>$domain->domain), 2)));

        if ($domain->id) {
            return Output::push(array(
                'path' => 'domain',
                'messages' => array('success' => _('You have added domain successfully')),
            ));
        } else {
            return Output::push(array(
                'path' => 'domain/add',
                'messages' => array('fail' => _('Fail to add domain')),
                'input' => TRUE,
            ));
        }
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function getUsers($id)
	{
        $users = User::where('domain_id',$id)->get();

        return View::make('domain.users')->with('users',$users);
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function getEdit($id)
	{
        $domain = Domain::find($id);

        return View::make('domain.edit')->with('domain',$domain);
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        $input = Input::only('domain', 'description');

        $rules = array(
            //'domain' => 'required|unique:domains,domain,'.$id,
        );
        $v = Validator::make($input, $rules);
        if ($v->fails()) {
            return Output::push(array('path' => 'domain/edit/'.$id, 'errors' => $v, 'input' => TRUE));
        }

        $domain = Domain::find($id);
        //$domain->domain = $input['domain'];
        $domain->description = $input['description'];
        $domain->save();

        if ($id) {
            return Output::push(array(
                'path' => 'domain',
                'messages' => array('success' => _('You have updated domain successfully')),
            ));
        } else {
            return Output::push(array(
                'path' => 'domain/edit/'.$id,
                'messages' => array('fail' => _('Fail to update domain')),
                'input' => TRUE,
            ));
        }
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function getDelete($id)
	{
        $domain = Domain::find($id);
        $domain->delete();
//        Domain::destroy($id);
        User::whereDomainId($id)->delete();

        Event::fire('logger',array(array('domain_remove',array('id'=>$id,'domain_name'=>$domain->domain),2)));

        return Output::push(array(
            'path' => 'domain',
            'messages' => array('success' => _('Domain has been deleted'))
        ));
	}

    private function generate_prefix()
    {
        $prefix = explode(',', str_replace(" ","",Config::get('settings.reserved_domain_prefix')));
        foreach(Domain::all() as $domain){
            $prefix[] = $domain['prefix'];
        }

        $rand_prefix = rand(1,9).rand(1,9).rand(1,9);
        if(in_array($rand_prefix, $prefix)){
            $this->generate_prefix();
        }else{
            return $rand_prefix;
        }
    }


}

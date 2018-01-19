<?php

namespace CSUNMetaLab\Support\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;

use Illuminate\Support\Facades\Mail;

use CSUNMetaLab\Support\Exceptions\SupportModelNotFoundException;

use CSUNMetaLab\Support\Http\Requests\SupportFormRequest;

class SupportController extends BaseController
{
	/**
	 * Displays the form to submit a support request message.
	 *
	 * @return View
	 */
	public function create() {

	}

	/**
	 * Accepts the support request submission and redirects back upon success.
	 *
	 * @return RedirectResponse
	 */
	public function store(SupportFormRequest $request) {
		$content = $request->input('content');

		// retrieve the user attributes dynamically
		$idAttr = config('support.submitter.id', 'id');
		$nameAttr = config('support.submitter.name', 'name');
		$emailAttr = config('support.submitter.email', 'email');
		$user_id = auth()->user()->$idAttr;
		$name = auth()->user()->$nameAttr;
		$email = auth()->user()->$emailAttr;

		// resolve the impact key and value
		$impactArr = unserialize(config('support.impact'));
		$impactKey = $request->input('impact');
		$impactVal = $impactArr[$impactKey];

		// determine what to report as the application name
		$appName = config('app.name', 'Laravel');
		if(config('support.allow_application_name_override')) {
			if($request->input('application_name', null)) {
				$appName = $request->input('application_name');
			}
		}

		if(class_exists('Illuminate\Mail\Mailable')) {
			// use an instance of a custom Mailable instance that is also
			// queueable
		}
		else
		{
			// send the email using the Mail facade and the queue() method
		}

		// write the record to the database if database support is enabled
		if(config('support.database.enabled')) {
			$model = config('support.database.models.support');
			if(class_exists($model)) {
				$model::create([
					'application_name' => $appName,
					'user_id' => $user_id,
					'impact' => $impactKey,
					'content' => $content,
				]);
			}
			else
			{
				// model could not be resolved, so log out the error and then
				// throw a catchable exception
				$msg = trans('support.errors.support.model_not_found', [
					'model' => $model
				]);
				logger()->error($msg);
				throw new SupportModelNotFoundException($msg);
			}
		}

		// there was some kind of success, so re-direct back to the form
		return redirect()->back()->with('message',
			trans('support.success.support'));
	}
}
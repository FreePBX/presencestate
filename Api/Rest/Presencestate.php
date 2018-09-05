<?php
namespace FreePBX\modules\Presencestate\Api\Rest;
use FreePBX\modules\Api\Rest\Base;
class Presencestate extends Base {
	protected $module = 'presencestate';
	public function setupRoutes($app) {
		/**
		* @verb GET
		* @returns - a list of presencestates
		* @uri /presencestate/list
		*/
		$app->get('/list', function ($request, $response, $args) {
			\FreePBX::Modules()->loadFunctionsInc('presencestate');
			$presencestates = presencestate_list_get();

			$presencestates = $presencestates ? $presencestates : false;
			return $response->withJson($presencestates);
		})->add($this->checkAllReadScopeMiddleware());

		/**
		 * @verb GET
		 * @returns - a list of presencestate types
		 * @uri /presencestate/types
		 */
		$app->get('/types', function ($request, $response, $args) {
			\FreePBX::Modules()->loadFunctionsInc('presencestate');
			$types = presencestate_types_get();

			$types = $types ? $types : false;
			return $response->withJson($types);
		})->add($this->checkAllReadScopeMiddleware());

		/**
		 * @verb GET
		 * @returns - a users presencestate preferences
		 * @uri /presencestate/prefs/:extension
		 */
		$app->get('/presencestate/prefs/{extension}', function ($request, $response, $args) {
			\FreePBX::Modules()->loadFunctionsInc('presencestate');
			$prefs = presencestate_prefs_get($args['extension']);

			$prefs = $prefs ? $prefs : false;
			return $response->withJson($prefs);
		})->add($this->checkAllReadScopeMiddleware());

		/**
		* @verb PUT
		* @uri /presencestate/prefs/:extension
		*/
		$app->put('/presencestate/prefs/{extension}', function ($request, $response, $args) {
			\FreePBX::Modules()->loadFunctionsInc('presencestate');
			$params = $request->getParsedBody();
			return $response->withJson(presencestate_prefs_set($args['extension'], $params));
		})->add($this->checkAllWriteScopeMiddleware());
	}
}

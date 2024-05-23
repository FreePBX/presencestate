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

			$presencestates = $presencestates ?? false;
			$response->getBody()->write(json_encode($presencestates));
			return $response->withHeader('Content-Type', 'application/json');
		})->add($this->checkAllReadScopeMiddleware());

		/**
		 * @verb GET
		 * @returns - a list of presencestate types
		 * @uri /presencestate/types
		 */
		$app->get('/types', function ($request, $response, $args) {
			\FreePBX::Modules()->loadFunctionsInc('presencestate');
			$types = presencestate_types_get();

			$types = $types ?? false;
			$response->getBody()->write(json_encode($types));
			return $response->withHeader('Content-Type', 'application/json');
		})->add($this->checkAllReadScopeMiddleware());

		/**
		 * @verb GET
		 * @returns - a users presencestate preferences
		 * @uri /presencestate/prefs/:extension
		 */
		$app->get('/presencestate/prefs/{extension}', function ($request, $response, $args) {
			\FreePBX::Modules()->loadFunctionsInc('presencestate');
			$prefs = presencestate_prefs_get($args['extension']);

			$prefs = $prefs ?? false;
			$response->getBody()->write(json_encode($prefs));
			return $response->withHeader('Content-Type', 'application/json');
		})->add($this->checkAllReadScopeMiddleware());

		/**
		* @verb PUT
		* @uri /presencestate/prefs/:extension
		*/
		$app->put('/presencestate/prefs/{extension}', function ($request, $response, $args) {
			\FreePBX::Modules()->loadFunctionsInc('presencestate');
			$params = $request->getParsedBody();
			$response->getBody()->write(json_encode(presencestate_prefs_set($args['extension'] ?? '', $params)));
			return $response->withHeader('Content-Type', 'application/json');
		})->add($this->checkAllWriteScopeMiddleware());
	}
}

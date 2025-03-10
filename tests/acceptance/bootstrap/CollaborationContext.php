<?php declare(strict_types=1);
/**
 * ownCloud
 *
 * @author Amrita Shrestha <amrita@jankaritech.com>
 * @copyright Copyright (c) 2024 Amrita Shrestha <amrita@jankaritech.com>
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License,
 * as published by the Free Software Foundation;
 * either version 3 of the License, or any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>
 *
 */

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use GuzzleHttp\Exception\GuzzleException;
use TestHelpers\HttpRequestHelper;

/**
 * steps needed to re-configure oCIS server
 */
class CollaborationContext implements Context {
	private FeatureContext $featureContext;
	private SpacesContext $spacesContext;

	/**
	 * This will run before EVERY scenario.
	 * It will set the properties for this object.
	 *
	 * @BeforeScenario
	 *
	 * @param BeforeScenarioScope $scope
	 *
	 * @return void
	 */
	public function before(BeforeScenarioScope $scope): void {
		// Get the environment
		$environment = $scope->getEnvironment();
		// Get all the contexts you need in this context from here
		$this->featureContext = $environment->getContext('FeatureContext');
		$this->spacesContext = $environment->getContext('SpacesContext');
	}

	/**
	 * @When user :user checks the information of file :file of space :space using office :app
	 *
	 * @param string $user
	 * @param string $file
	 * @param string $space
	 * @param string $app
	 *
	 * @return void
	 *
	 * @throws GuzzleException
	 * @throws JsonException
	 */
	public function userChecksTheInformationOfFileOfSpaceUsingOffice(string $user, string $file, string $space, string $app): void {
		$fileId = $this->spacesContext->getFileId($user, $space, $file);
		$response = \json_decode(
			HttpRequestHelper::post(
				$this->featureContext->getBaseUrl() . "/app/open?app_name=$app&file_id=$fileId",
				$this->featureContext->getStepLineRef(),
				$this->featureContext->getActualUsername($user),
				$this->featureContext->getPasswordForUser($user),
				['Content-Type' => 'application/json']
			)->getBody()->getContents()
		);

		$accessToken = $response->form_parameters->access_token;

		// Extract the WOPISrc from the app_url
		$parsedUrl = parse_url($response->app_url);
		parse_str($parsedUrl['query'], $queryParams);
		$wopiSrc = $queryParams['WOPISrc'];

		$this->featureContext->setResponse(
			HttpRequestHelper::get(
				$wopiSrc . "?access_token=$accessToken",
				$this->featureContext->getStepLineRef()
			)
		);
	}
}

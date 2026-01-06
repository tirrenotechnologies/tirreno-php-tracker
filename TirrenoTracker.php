<?php

/**
 * tirreno ~ open security analytics
 * Copyright (c) Tirreno Technologies Sàrl (https://www.tirreno.com)
 *
 * @copyright     Copyright (c) tirreno technologies sàrl (https://www.tirreno.com)
 * @license       https://opensource.org/licenses/bsd-3-clause BSD License
 * @link          https://www.tirreno.com tirreno(tm)
 */

/**
 * TirrenoTracker implements the tirreno tracking API.
 *
 * For more information, see: https://github.com/tirrenotechnologies/tirreno-php-tracker/
 */

final class TirrenoTracker {
    private $apiUrl;
    private $apiKey;

    private string $userName;
    private string $eventTime;

    private string $ipAddress;
    private string $userAgent;
    private string $browserLanguage;
    private string $httpMethod;
    private string $httpReferer;
    private string $url;

    private ?string $pageTitle;
    private ?string $fullName;
    private ?string $firstName;
    private ?string $lastName;
    private ?string $emailAddress;
    private ?string $phoneNumber;
    private ?string $eventType;
    private ?string $httpCode;

    private ?array $payload;
    private ?array $fieldHistory;

    private const LIST_OF_SERVER_IP_KEYS = [
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_FORWARDED_FOR',
        'HTTP_CLIENT_IP',
    ];

    private const DEFAULT_PROPERTIES = [
        'userName',
        'eventTime',
        'ipAddress',
        'userAgent',
        'browserLanguage',
        'httpMethod',
        'httpReferer',
        'url',
    ];

    private const OPTIONAL_PROPERTIES = [
        'pageTitle',
        'fullName',
        'firstName',
        'lastName',
        'emailAddress',
        'phoneNumber',
        'eventType',
        'httpCode',
        'payload',
        'fieldHistory'
    ];

    public function __construct(string $apiUrl, string $apiKey) {
        $this->apiUrl = $this->trackingUrl($apiUrl);
        $this->apiKey = $apiKey;

        $this->setDefaultEvent();
    }

    public function setDefaultEvent(): void {
        $this->ipAddress = $this->getIpAddress(true);
        $this->eventTime = $this->getEventTime(true);
        $this->userAgent = $this->getUserAgent(true);
        $this->browserLanguage = $this->getBrowserLanguage(true);
        $this->httpMethod = $this->getHttpMethod(true);
        $this->httpReferer = $this->getHttpReferer(true);
        $this->url = $this->getUrl(true);

        $this->setEventTypePageView();

        foreach (self::OPTIONAL_PROPERTIES as $prop) {
            $this->$prop = null;
        }
    }

    public function dump(): array {
        $data = [];

        if (!isset($this->userName) && !isset($this->emailAddress)) {
            $this->userName = $this->getUserName(true);
        }

        foreach (self::DEFAULT_PROPERTIES as $prop) {
            $data[$prop] = $this->$prop;
        }

        foreach (self::OPTIONAL_PROPERTIES as $prop) {
            if (isset($this->$prop) && $this->$prop !== null) {
                $data[$prop] = $this->$prop;
            }
        }

        return $data;
    }

    public function track(): void {
        if (function_exists('curl_init')) {
            $headers = [
                'Api-Key: ' . $this->apiKey,
                'Content-Type: application/x-www-form-urlencoded',
            ];
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL             => $this->apiUrl,
                CURLOPT_RETURNTRANSFER  => false,
                CURLOPT_POST            => true,
                CURLOPT_POSTFIELDS      => http_build_query($this->dump()),
                CURLOPT_TIMEOUT_MS      => 1500,
                CURLOPT_HTTPHEADER      => $headers,
            ]);
            curl_exec($ch);
            curl_close($ch);
        }
    }

    public function getEventTime(bool $default = false): string {
        if (!isset($this->eventTime) || $default) {
            $utcTimeZone = new \DateTimeZone('UTC');
            $timestamp = \DateTime::createFromFormat('U.u', sprintf('%.6F', $_SERVER['REQUEST_TIME_FLOAT']));
            $timestamp->setTimezone($utcTimeZone);

            return $timestamp->format('Y-m-d H:i:s.v');
        }

        return $this->eventTime;
    }

    public function setEventTime(string $eventTime): self {
        $this->eventTime = $eventTime;

        return $this;
    }

    public function getUserName(bool $default = false): string {
        if (!isset($this->userName) || $default) {
            return $this->maskIp($this->ipAddress);
        }

        return $this->userName;
    }

    public function setUserName(string $userName): self {
        $this->userName = $userName;

        return $this;
    }

    public function getIpAddress(bool $default = false): string {
        return (!isset($this->ipAddress) || $default) ? $this->getRequestIp() : $this->ipAddress;
    }

    public function setIpAddress(string $ip): self {
        $this->ipAddress = $ip;

        return $this;
    }

    public function getUserAgent(bool $default = false): string {
        if (!isset($this->userAgent) || $default) {
            return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        }

        return $this->userAgent;
    }

    public function setUserAgent(string $ua): self {
        $this->userAgent = $ua;

        return $this;
    }

    public function getBrowserLanguage(bool $default = false): string {
        if (!isset($this->browserLanguage) || $default) {
            return isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';
        }

        return $this->browserLanguage;
    }

    public function setBrowserLanguage(string $lang): self {
        $this->browserLanguage = $lang;

        return $this;
    }

    public function getHttpMethod(bool $default = false): string {
        if (!isset($this->httpMethod) || $default) {
            return isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '';
        }

        return $this->httpMethod;
    }

    public function setHttpMethod(string $httpMethod): self {
        $this->httpMethod = $httpMethod;

        return $this;
    }

    public function getHttpReferer(bool $default = false): string {
        if (!isset($this->httpReferer) || $default) {
            return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        }

        return $this->httpReferer;
    }

    public function setHttpReferer(string $referer): self {
        $this->httpReferer = $referer;

        return $this;
    }

    public function getUrl(bool $default = false): string {
        if (!isset($this->url) || $default) {
            return isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
        }

        return $this->url;
    }

    public function setUrl(string $url): self {
        $this->url  = $url;

        return $this;
    }

    public function getPageTitle(): ?string {
        return $this->pageTitle;
    }

    public function setPageTitle(?string $pageTitle): self {
        $this->pageTitle = $pageTitle;

        return $this;
    }

    public function getFullName(): ?string {
        return $this->fullName;
    }

    public function setFullName(?string $fullName): self {
        $this->fullName = $fullName;

        return $this;
    }

    public function getFirstName(): ?string {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmailAddress(): ?string {
        return $this->emailAddress;
    }

    public function setEmailAddress(?string $emailAddress): self {
        $this->emailAddress = $emailAddress;

        return $this;
    }

    public function getPhoneNumber(): ?string {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): self {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getEventType(): ?string {
        return $this->eventType;
    }

    public function setEventType(string $eventType): self {
        $this->eventType = $eventType;

        return $this;
    }

    public function getHttpCode(): ?string {
        return $this->httpCode;
    }

    public function setHttpCode(string|int $httpCode): self {
        $this->httpCode = (string) $httpCode;

        return $this;
    }

    public function getPayload(): ?array {
        return $this->payload;
    }

    public function setPayload(array $payload): self {
        $this->payload  = $payload;

        return $this;
    }

    public function addPayload(array $item): self {
        if (!isset($this->payload)) {
            $this->payload = [];
        }

        $this->payload[] = $item;

        return $this;
    }

    public function getFieldHistory(): ?array {
        return $this->fieldHistory;
    }

    public function setFieldHistory(array $fieldHistory): self {
        $this->fieldHistory  = $fieldHistory;

        return $this;
    }

    public function addFieldHistory(array $item): self {
        if (!isset($this->fieldHistory)) {
            $this->fieldHistory = [];
        }

        $this->fieldHistory[] = $item;

        return $this;
    }

    private function maskIp(string $ip): string {
        if (strpos($ip, ':') !== false) {
            $parts = explode(':', $ip);
            if (count($parts) > 1) {
                $parts[count($parts) - 1] = '*';
            }
            return implode(':', $parts);
        } else {
            $parts = explode('.', $ip);
            if (count($parts) === 4) {
                $parts[3] = '*';
            }
            return implode('.', $parts);
        }

        return $ip;
    }

    private function isIp(string $ip): bool {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
    }

    private function trackingUrl(string $url): string {
        $url = str_ends_with($url, '/') ? $url : $url . '/';

        if (!str_ends_with($url, '/sensor/')) {
            $url .= 'sensor/';
        }

        return $url;
    }

    private function getRequestIp(): string {
        if (isset($_SERVER['REMOTE_ADDR']) && $this->isIp($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }

        if (isset($_SERVER['HTTP_FORWARDED']) && preg_match('/for=([\[\]A-F0-9\.:]+)/i', $_SERVER['HTTP_FORWARDED'], $matches)) {
            $value = trim($matches[1], '"');
            if ($this->isIp($value)) {
                return $value;
            }
        }

        foreach (self::LIST_OF_SERVER_IP_KEYS as $key) {
            if (isset($_SERVER[$key])) {
                $ip = trim(explode(',', $_SERVER[$key])[0]);
                if ($this->isIp($ip)) {
                    return $ip;
                }
            }
        }

        return '127.0.0.1';
    }

    public function setEventTypePageView(): self {
        $this->eventType = 'page_view';

        return $this;
    }

    public function setEventTypePageEdit(): self {
        $this->eventType = 'page_edit';

        return $this;
    }

    public function setEventTypePageDelete(): self {
        $this->eventType = 'page_delete';

        return $this;
    }

    public function setEventTypePageSearch(): self {
        $this->eventType = 'page_search';

        return $this;
    }

    public function setEventTypeAccountLogin(): self {
        $this->eventType = 'account_login';

        return $this;
    }

    public function setEventTypeAccountLogout(): self {
        $this->eventType = 'account_logout';

        return $this;
    }

    public function setEventTypeAccountLoginFail(): self {
        $this->eventType = 'account_login_fail';

        return $this;
    }

    public function setEventTypeAccountRegistration(): self {
        $this->eventType = 'account_registration';

        return $this;
    }

    public function setEventTypeAccountEmailChange(): self {
        $this->eventType = 'account_email_change';

        return $this;
    }

    public function setEventTypeAccountPasswordChange(): self {
        $this->eventType = 'account_password_change';

        return $this;
    }

    public function setEventTypeAccountEdit(): self {
        $this->eventType = 'account_edit';

        return $this;
    }

    public function setEventTypePageError(): self {
        $this->eventType = 'page_error';

        return $this;
    }

    public function setEventTypeFieldEdit(): self {
        $this->eventType = 'field_edit';

        return $this;
    }
}

<?php

namespace AdsTxtParser;

use AdsTxtParser\Exception\AdsFileNotFound;

/**
 * AdsTxtParser
 *
 * Compatible with Ads.txt Specification Version 1.0.1 (OpenRTB working group)
 * https://iabtechlab.com/wp-content/uploads/2017/09/IABOpenRTB_Ads.txt_Public_Spec_V1-0-1.pdf
 *
 * @see https://iabtechlab.com/ads-txt/
 * @author Ángel Guzmán Maeso <angel@guzmanmaeso.com>
 *
 * @license AGPLv3
 */
class Parser
{
    private $comments = [];
    private $variables = [];
    private $fields = [];
    private $warnings = [];
    private $errors = [];
    private $resellers = [];
    private $directs = [];

    /**
     * Get the comments variables in the parsing of a file
     *
     * @return array
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Get the fields variables in the parsing of a file
     *
     * @return array
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * Get the fields produced in the parsing of a file
     *
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Get the warnings produced in the parsing of a file
     *
     * @return array
     */
    public function getWarnings()
    {
        return $this->warnings;
    }

    /**
     * Get the errors produced in the parsing of a file
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    public function getResellers()
    {
        if(empty($this->resellers))
        {
            if(!empty($this->getFields()))
            {
                foreach($this->getFields() as $record)
                {
                    if(isset($record['fields']['relationship']))
                    {
                        $relationship = $record['fields']['relationship'];
                        if(strtolower($relationship) === 'reseller')
                        {
                            $this->resellers[] = $record;
                        }
                    }
                }
            }
        }
        return $this->resellers;
    }

    public function getDirects()
    {
        if(empty($this->directs))
        {
            $fields = $this->getFields();
            if(!empty($fields))
            {
                foreach($fields as $record)
                {
                    if(isset($record['fields']['relationship']))
                    {
                        $relationship = $record['fields']['relationship'];
                        if(strtolower($relationship) === 'direct')
                        {
                            $this->directs[] = $record;
                        }
                    }
                }
            }
        }

        return $this->directs;
    }

    /**
     * Parse a string building and analyzing a structure following
     * the spec.
     *
     * @param string $data
     * @throws \Exception
     */
    public function parseString(string $data = NULL)
    {
        // A non-empty set of records, separated by line breaks
        $lines = explode(PHP_EOL, $data);

        if(empty($lines) || count($lines) === 0)
        {
            throw new \Exception('Empty ads.txt file');
        }
        else
        {
            /**
             * @todo
             3.4.3 EXTENSION FIELDS
             Extension fields are allowed by implementers and their consumers as long as they utilize a
             distinct final separator field ";" before adding extension data to each record.
             */
            foreach($lines as $lineNumber => $value)
            {
                $value = trim($value); // Clean spaces

                if(empty($value))
                {
                    continue;
                }

                // Lines starting with # symbol are considered comments and are ignored
                if(isset($value[0]) && $value[0] === '#')
                {
                    $this->comments[] = ['line' => $lineNumber, 'value' => $value];
                }
                else
                {
                    /**
                     * Comment are denoted by the character "#". Any line containing "#" should inform the data
                     * consumer to ignore the data after the "#" character to the end of the line.
                     */
                    if (FALSE !==  strpos($value, '#') )
                    {
                        $parts = explode('#', $value);
                        $numberParts = count($parts);
                        $remain = array_slice($parts, 1, $numberParts - 1);
                        $remain = reset($remain);
                        $this->comments[] = ['line' => $lineNumber, 'value' => $remain];
                        $value = trim($parts[0]);
                    }

                    // Lines containing the data format have syntax defined in section 3.4
                    // <FIELD #1>, <FIELD #2>, <FIELD #3>, <FIELD #4>
                    if (FALSE !==  strpos($value, ',') )
                    {
                        $fields = explode(',', $value);

                        if(count($fields) > 4)
                        {
                            $this->warnings[] = ['line' => $lineNumber, 'value' => $value, 'reason' => 'Fields should be 4 or less. Potential syntax error with double line'];
                        }

                        if(count($fields) < 4)
                        {
                            $missingFields = array_fill(count($fields), 4, NULL);
                            $fields = array_merge($fields, $missingFields);
                        }

                        $relationship = isset($fields[2]) ? trim(strtolower($fields[2])) : NULL;

                        if(!in_array($relationship, ['direct', 'reseller']))
                        {
                            $this->warnings[] = ['line' => $lineNumber, 'value' => $value, 'reason' => 'Relationship value should be only direct or reseller'];
                        }

                        $this->fields[] = [
                            'line' => $lineNumber,
                            'fields' => [
                                // Domain name of the advertising system
                                'domain' => isset($fields[0]) ? urlencode(trim($fields[0])) : NULL,
                                /**
                                 (Required) The canonical domain name of the
                                 SSP, Exchange, Header Wrapper, etc system that
                                 bidders connect to. This may be the operational
                                 domain of the system, if that is different than the
                                 parent corporate domain, to facilitate WHOIS and
                                 reverse IP lookups to establish clear ownership of
                                 the delegate system. Ideally the SSP or Exchange
                                 publishes a document detailing what domain name
                                 to use.
                                 */

                                // Publisher’s Account ID
                                'publisher_account_id' => isset($fields[1]) ? urlencode(trim($fields[1])) : NULL,
                                /**
                                 (Required) The identifier associated with the seller
                                 or reseller account within the advertising system in
                                 field #1. This must contain the same value used in
                                 transactions (i.e. OpenRTB bid requests) in the
                                 field specified by the SSP/exchange. Typically, in
                                 OpenRTB, this is publisher.id. For OpenDirect it is
                                 typically the publisher’s organization ID.
                                 */

                                // Type of Account/Relationship
                                'relationship' => urlencode($relationship),
                                /*
                                 (Required) An enumeration of the type of account.
                                 A value of ‘DIRECT’ indicates that the Publisher
                                 (content owner) directly controls the account
                                 indicated in field #2 on the system in field #1. This
                                 tends to mean a direct business contract between
                                 the Publisher and the advertising system. A value
                                 of ‘RESELLER’ indicates that the Publisher has
                                 authorized another entity to control the account
                                 indicated in field #2 and resell their ad space via
                                 the system in field #1. Other types may be added
                                 in the future. Note that this field should be treated
                                 as case insensitive when interpreting the data.
                                 */

                                // Certification Authority ID
                                'certification_authority_id' => isset($fields[3]) ? urlencode(trim($fields[3])) : NULL,
                                /*
                                 (Optional) An ID that uniquely identifies the
                                 advertising system within a certification authority
                                 (this ID maps to the entity listed in field #1). A
                                 current certification authority is the Trustworthy
                                 Accountability Group (aka TAG), and the TAGID
                                 would be included here [11].
                                 */
                            ]
                        ];
                    }
                    // Lines containing the variable format have syntax defined in section 3.5
                    // <VARIABLE>=<VALUE>

                    /**
                     *  VARIABLE => CONTACT
                     *  VALUE => Contact information
                     *  DESCRIPTION
                     *  (Optional) Some human readable contact
                     information for the owner of the file. This may be
                     the contact of the advertising operations team for
                     the website. This may be an email address,
                     phone number, link to a contact form, or other
                     suitable means of communication.
                     *
                     *  VARIABLE => SUBDOMAIN
                     *  VALUE => Pointer to a subdomain file
                     *  DESCRIPTION
                     *  (Optional) A machine readable subdomain pointer to a subdomain within the
                     *  root domain, on which an ads.txt can be found. The crawler should fetch
                     and consume associate the data to the
                     subdomain, not the current domain. This referral
                     should be exempt from the public suffix truncation
                     process. Only root domains should refer crawlers
                     to subdomains. Subdomains should not refer to
                     other subdomains.
                     */
                    elseif (FALSE !==  strpos($value, '=') )
                    {
                        /**
                         * Comment are denoted by the character "#". Any line containing "#" should inform the data
                         * consumer to ignore the data after the "#" character to the end of the line.
                         */
                        if (FALSE !==  strpos($value, '#') )
                        {
                            $parts = explode('#', $value);
                            $numberParts = count($parts);
                            $remain = array_slice($parts, 1, $numberParts - 1);
                            $remain = reset($remain);
                            $this->comments[] = ['line' => $lineNumber, 'value' => $remain];
                            $value = trim($parts[0]);
                        }

                        $parts = explode('=', $value);

                        // Assume that only a symbol = is parsed, and remain is a value (@ŧodo bug in spec?)
                        $numberParts = count($parts);
                        if($numberParts > 2)
                        {
                            $variable = trim($parts[0]);

                            // The <VARIABLE> is a string identifier without internal whitespace.
                            $variable = str_replace(' ', '', $variable);

                            $remain = array_slice($parts, 1, $numberParts - 1);

                            $this->warnings[] = ['line' => $lineNumber, 'value' => $value, 'reason' => 'Only a symbol = should be used'];
                        }
                        else
                        {
                            $variable = trim($parts[0]);

                            // The <VARIABLE> is a string identifier without internal whitespace.
                            $variable = str_replace(' ', '', $variable);
                            $remain = $parts[1];
                        }

                        $finalValue = urlencode(trim(implode('=', $remain)));

                        if(!in_array(strtolower($variable), ['contact', 'subdomain']))
                        {
                            $this->errors[] = ['line' => $lineNumber, 'value' => $value, 'reason' => 'Valiable names supported are CONTACT or SUBDOMAIN.'];
                            continue;
                        }

                        $this->variables[] = ['line' => $lineNumber, 'variable' => $variable, 'value' => $finalValue];
                    }
                    else
                    {
                        $this->errors[] = ['line' => $lineNumber, 'value' => $value, 'reason' => 'Format invalid for data or variable format'];
                    }
                }
            }
        }
    }

    public function readExternalFile(string $domain = 'http://localhost')
    {
        $fileName = $domain . '/ads.txt';
        $adsTxtFile = @file_get_contents($fileName);

        if(FALSE === $adsTxtFile)
        {
            throw new AdsFileNotFound('Error getting ads.txt file for the domain');
        }
        elseif(empty($adsTxtFile))
        {
            throw new \Exception('Empty ads.txt file');
        }
        else
        {
            /**
             * Optional check if available fileinfo extension
             *
             * apt-get install libmagic1-dev
             * pecl install Fileinfo
             * Add "extension=fileinfo.so" to php.ini (/etc/php5/{cli,cgi}/php.ini)
             * ln -s /usr/share/file/magic /etc/magic.mime
             */
            if(extension_loaded('fileinfo'))
            {
                $finfo = @finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = @finfo_file($finfo, $fileName);

                if(FALSE != $mimeType && $mimeType != 'text/plain')
                {
                    throw new \Exception('MIMETYPE should be text/plain');
                }
                @finfo_close($finfo);
            }
            /*
            else // This could be A local alternative
            {
                $mimeType = system("file -i -b ads.txt");

                if($mimeType != 'text/plain')
                {
                    throw new \Exception('MIMETYPE should be text/plain');
                }
            }
            */

            $this->parseString($adsTxtFile);
        }
    }
}
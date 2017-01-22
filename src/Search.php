<?php
/**
* @author         Pierre-Henry Soria <pierrehenrysoria@gmail.com>
* @copyright      (c) 2017, Pierre-Henry Soria. All Rights Reserved.
* @license        GNU General Public License; <https://www.gnu.org/licenses/gpl-3.0.en.html>
 */
namespace PH7\CompaniesLookup;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Netsensia\CompaniesHouse\Api\Client\Client;

class Search extends Command
{
    private $sCompanyName;
    private $iCompanyId;
    private $oData;
    private $oClient;

    public function __construct(Client $oClient)
    {
        parent::__construct();
        $this->oClient = $oClient;
    }

    /**
     * Configure the arguments.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('companies:search')
            ->setDescription('Search a company by its "name" or "company ID"')
            ->addArgument(
                'company',
                InputArgument::REQUIRED
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setCompanyArgument($input);

        $this->setCompanyData($input, $output);

        $this->showName($output);
        $this->companyNumber($output);
        $this->companyType($output);
        $this->showCompanyStatus($output);
        $this->showDateOfCreation($output);
        $this->showAddress($output);
    }

    protected function showName(OutputInterface $output)
    {
        if (!empty($this->oData->title)) {
            $sName =$this->oData->title;
        } else {
            $sName = $this->oData->company_name;
        }

        if (!empty($sName)) {
            $output->writeln('<info>Full Company Name: ' . htmlspecialchars($sName) . '</info>');
        } else {
            $this->notFound('Company Name', $output);
        }
    }

    protected function companyNumber(OutputInterface $output)
    {
        if (!empty($this->oData->company_number)) {
            $output->writeln('<info>Company Number: ' . htmlspecialchars($this->oData->company_number) . '</info>');
        } else {
            $this->notFound('Company Number', $output);
        }
    }

    protected function companyType(OutputInterface $output)
    {
        if (!empty($this->oData->company_type)) {
            $sType = $this->oData->company_type;
        } else {
            $sType = $this->oData->type;
        }

        if (!empty($sType)) {
            $output->writeln('<info>Company Type: ' . strtoupper(htmlspecialchars($sType)) . '</info>');
        } else {
            $this->notFound('Company Type', $output);
        }
    }

    protected function showCompanyStatus(OutputInterface $output)
    {
        if (!empty($this->oData->company_status)) {
            $output->writeln('<info>Company Status: ' . ucfirst(htmlspecialchars($this->oData->company_status)) . '</info>');
        } else {
            $this->notFound('Company Status', $output);
        }
    }

    protected function showAddress(OutputInterface $output)
    {
        if (!empty($this->oData->registered_office_address)) {
            $oAddress = $this->oData->registered_office_address;
        } else {
            $oAddress = $this->oData->address;
        }

        if (!empty($oAddress->address_line_1)) {
            $output->writeln('<info>Address: ' . htmlspecialchars($oAddress->address_line_1) . '</info>');

            if (!empty($oAddress->country)) {
                $output->writeln('<info>Country: ' . htmlspecialchars($oAddress->country) . '</info>');
            }
            if (!empty($oAddress->postal_code)) {
                $output->writeln('<info>Postal Code: ' . htmlspecialchars($oAddress->postal_code) . '</info>');
            }
            if (!empty($oAddress->locality)) {
                $output->writeln('<info>Locality: ' . htmlspecialchars($oAddress->locality) . '</info>');
            }
        } else {
            $this->notFound('Address', $output);
        }
    }

    protected function showDateOfCreation(OutputInterface $output)
    {
        if (!empty($this->oData->date_of_creation)) {
            $output->writeln('<info>Date Of Creation: ' . htmlspecialchars($this->oData->date_of_creation) . '</info>');
        } else {
            $this->notFound('Date Of Creation', $output);
        }
    }

    protected function setCompanyData(InputInterface $input, OutputInterface $output)
    {
        if (!empty($this->iCompanyId)) {
            $oProfile = $this->oClient->getCompanyProfile($this->iCompanyId);
            if (!$oProfile) {
                $this->displayCompanyNotFound($input, $output);
                exit(1);
            } else {
                $this->oData = $oProfile;
            }
        } else {
            $oProfile = $this->oClient->companySearch($this->sCompanyName);
            if (!$oProfile || empty($oProfile->items[0])) {
                $this->displayCompanyNotFound($input, $output);
                exit(1);
            } else {
                $this->oData = $oProfile->items[0];
            }
        }
    }

    protected function displayCompanyNotFound(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<error>Company "' . $input->getArgument('company') . '" not found.</error>');
    }

    protected function notFound(string $sFieldName, OutputInterface $output)
    {
        $output->writeln('<error>"' . $sFieldName . '" not found.</error>');
    }

    protected function setCompanyArgument(InputInterface $input)
    {
        $mCompany = $input->getArgument('company');

        if (is_numeric($mCompany) && strlen($mCompany) > 5) {
            $this->iCompanyId = $mCompany;
        } else {
            $this->sCompanyName = $mCompany;
        }
    }
}

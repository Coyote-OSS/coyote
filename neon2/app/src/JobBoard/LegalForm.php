<?php
namespace Neon2\JobBoard;

enum LegalForm: string {
    case EmploymentContract = 'employment';
    case ContractOfMandate = 'of-mandate';
    case ContractForSpecificTask = 'specific-task';
    case BusinessToBusiness = 'b2b';
}
